
<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = DB::connection($this->getConnection());

        $schema = Schema::connection($this->getConnection());

        $conversationsTable = config(
            'ai.conversations.tables.conversations',
            'agent_conversations'
        );

        $messagesTable = config(
            'ai.conversations.tables.messages',
            'agent_conversation_messages'
        );

        /*
         * STEP 1:
         * Remove existing indexes involving user_id.
         *
         * This avoids errors when index names differ
         * between staging and local databases.
         */

        if ($schema->hasColumn($conversationsTable, 'user_id')) {
            $this->dropIndexesContainingColumn(
                $conversationsTable,
                'user_id'
            );
        }

        if ($schema->hasColumn($messagesTable, 'user_id')) {
            $this->dropIndexesContainingColumn(
                $messagesTable,
                'user_id'
            );
        }

        /*
         * STEP 2:
         * Upgrade agent_conversations.
         */

        if (
            $schema->hasColumn($conversationsTable, 'user_id')
            && ! $schema->hasColumn($conversationsTable, 'participant_id')
        ) {
            $schema->table($conversationsTable, function (Blueprint $table) {
                $table->renameColumn('user_id', 'participant_id');
            });
        }

        if (! $schema->hasColumn($conversationsTable, 'participant_type')) {
            $schema->table($conversationsTable, function (Blueprint $table) {
                $table->string('participant_type',200)
                    ->nullable()
                    ->after('id');
            });
        }

        /*
         * STEP 3:
         * Upgrade agent_conversation_messages.
         */

        if (
            $schema->hasColumn($messagesTable, 'user_id')
            && ! $schema->hasColumn($messagesTable, 'participant_id')
        ) {
            $schema->table($messagesTable, function (Blueprint $table) {
                $table->renameColumn('user_id', 'participant_id');
            });
        }

        if (! $schema->hasColumn($messagesTable, 'participant_type')) {
            $schema->table($messagesTable, function (Blueprint $table) {
                $table->string('participant_type')
                    ->nullable()
                    ->after('conversation_id');
            });
        }

        /*
         * STEP 4:
         * Preserve existing participant ownership.
         */

        $participantType = (new User)->getMorphClass();

        $connection->table($conversationsTable)
            ->whereNotNull('participant_id')
            ->whereNull('participant_type')
            ->update([
                'participant_type' => $participantType,
            ]);

        $connection->table($messagesTable)
            ->whereNotNull('participant_id')
            ->whereNull('participant_type')
            ->update([
                'participant_type' => $participantType,
            ]);

        /*
         * STEP 5:
         * Remove old named indexes if they exist.
         */

        $this->dropIndexIfExists(
            $messagesTable,
            'conversation_index'
        );

        $this->dropIndexIfExists(
            $messagesTable,
            'participant_index'
        );

        $this->dropIndexIfExists(
            $conversationsTable,
            'participant_updated_at_index'
        );

        /*
         * STEP 6:
         * Add Laravel AI 1.x message columns.
         *
         * Use the string value directly instead of
         * importing MessageStatus.
         */

        if (! $schema->hasColumn($messagesTable, 'steps')) {
            $schema->table($messagesTable, function (Blueprint $table) {
                $table->longText('steps')->nullable();
            });
        }

        if (! $schema->hasColumn($messagesTable, 'status')) {
            $schema->table($messagesTable, function (Blueprint $table) {
                $table->string('status', 25)->default('completed');
            });
        }

        /*
         * STEP 7:
         * Initialize user messages.
         */

        $connection->table($messagesTable)
            ->where('role', 'user')
            ->update([
                'steps' => '[]',
            ]);

        /*
         * STEP 8:
         * Convert old tool_calls/tool_results
         * into the new steps structure.
         */

        if (
            $schema->hasColumn($messagesTable, 'tool_calls')
            && $schema->hasColumn($messagesTable, 'tool_results')
        ) {
            $connection->table($messagesTable)
                ->select('conversation_id')
                ->distinct()
                ->orderBy('conversation_id')
                ->chunk(100, function ($conversations) use ($messagesTable) {
                    foreach ($conversations as $conversation) {
                        $this->backfill(
                            $messagesTable,
                            $conversation->conversation_id
                        );
                    }
                });
        } else {
            // No legacy tool columns: initialize any remaining NULL steps.
            $connection->table($messagesTable)
                ->whereNull('steps')
                ->update(['steps' => '[]']);
        }

        /*
         * STEP 9:
         * Make steps mandatory and remove old columns.
         */

        $schema->table($messagesTable, function (Blueprint $table) use ($schema, $messagesTable) {
            $table->longText('steps')->nullable(false)->change();

            $oldColumns = [];

            foreach (['tool_calls', 'tool_results', 'approval_state'] as $column) {
                if ($schema->hasColumn($messagesTable, $column)) {
                    $oldColumns[] = $column;
                }
            }

            if (! empty($oldColumns)) {
                $table->dropColumn($oldColumns);
            }
        });

        /*
         * STEP 10:
         * Create indexes required by Laravel AI 1.x.
         */

        $schema->table($conversationsTable, function (Blueprint $table) {
            $table->index(
                ['participant_type', 'participant_id', 'updated_at'],
                'participant_updated_at_index'
            );
        });

        $schema->table($messagesTable, function (Blueprint $table) {
            $table->index(
                [
                    'conversation_id',
                    'participant_type',
                    'participant_id',
                    'updated_at',
                ],
                'conversation_index'
            );

            $table->index(
                ['participant_type', 'participant_id'],
                'participant_index'
            );
        });
    }

    /**
     * Drop every non-primary index containing a given column.
     */
    protected function dropIndexesContainingColumn(
        string $tableName,
        string $columnName
    ): void {
        $connection = DB::connection($this->getConnection());

        $wrappedTable = $connection->getQueryGrammar()
            ->wrapTable($tableName);

        $indexes = $connection->select(
            "SHOW INDEX FROM {$wrappedTable}"
        );

        $indexNames = collect($indexes)
            ->filter(fn ($index) =>
                $index->Column_name === $columnName
                && $index->Key_name !== 'PRIMARY'
            )
            ->pluck('Key_name')
            ->unique()
            ->values();

        foreach ($indexNames as $indexName) {
            Schema::connection($this->getConnection())
                ->table($tableName, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
        }
    }

    /**
     * Drop an index only when it exists.
     */
    protected function dropIndexIfExists(
        string $tableName,
        string $indexName
    ): void {
        $connection = DB::connection($this->getConnection());

        $wrappedTable = $connection->getQueryGrammar()
            ->wrapTable($tableName);

        $indexes = $connection->select(
            "SHOW INDEX FROM {$wrappedTable}"
        );

        $exists = collect($indexes)->contains(
            fn ($index) => $index->Key_name === $indexName
        );

        if ($exists) {
            Schema::connection($this->getConnection())
                ->table($tableName, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
        }
    }

    /**
     * Backfill existing assistant messages into steps.
     */
    protected function backfill(
        string $table,
        string $conversationId
    ): void {
        $connection = DB::connection($this->getConnection());

        $rows = $connection->table($table)
            ->where('conversation_id', $conversationId)
            ->where('role', 'assistant')
            ->orderBy('id')
            ->get();

        // Collect tool results from the conversation.
        $results = $rows
            ->flatMap(fn ($row) => $this->decoded($row->tool_results))
            ->keyBy('id');

        foreach ($rows as $row) {
            $meta = $this->decoded($row->meta);

            $calls = collect($this->decoded($row->tool_calls))
                ->filter(fn (array $call) =>
                    $results->has($call['id'] ?? '')
                )
                ->map(fn (array $call) => [
                    ...$call,
                    'result' => $results[$call['id']]['result'] ?? null,
                    ...array_filter([
                        'denied' => $results[$call['id']]['denied'] ?? false,
                        'failed' => $results[$call['id']]['failed'] ?? false,
                    ]),
                ])
                ->values()
                ->all();

            $content = (string) $row->content;

            $steps = $calls !== [] && $content !== ''
                ? [
                    $this->step('', $calls),
                    $this->step(
                        $content,
                        [],
                        $meta['reasoning'] ?? ''
                    ),
                ]
                : [
                    $this->step(
                        $content,
                        $calls,
                        $meta['reasoning'] ?? ''
                    ),
                ];

            unset(
                $meta['provider_steps'],
                $meta['provider_content_blocks'],
                $meta['reasoning']
            );

            $connection->table($table)
                ->where('id', $row->id)
                ->update([
                    'steps' => json_encode($steps),
                    'meta' => json_encode($meta),
                ]);
        }
    }

    /**
     * Build an SDK 1.x step.
     */
    protected function step(
        string $content,
        array $calls = [],
        string $reasoning = ''
    ): array {
        return [
            'content' => $content,
            'tool_calls' => $calls,
            'reasoning' => $reasoning,
            'replay_blocks' => [],
            'provider_tool_calls' => [],
        ];
    }

    /**
     * Decode JSON safely.
     */
    protected function decoded(?string $json): array
    {
        $decoded = json_decode($json ?? '', true);

        return is_array($decoded) ? $decoded : [];
    }

    public function down(): void
    {
        throw new RuntimeException(
            'Manual rollback is required for this Laravel AI SDK upgrade.'
        );
    }
};
