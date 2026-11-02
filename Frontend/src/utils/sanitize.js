/**
 * Comprehensive DOM sanitizer to prevent Cross-Site Scripting (XSS).
 * Neutralizes malicious tags, inline event listeners, and dangerous protocol URLs.
 */
export function sanitizeHtml(rawHtml) {
  if (!rawHtml || typeof rawHtml !== 'string') return ''

  if (typeof window === 'undefined' || !window.DOMParser) {
    return rawHtml.replace(/<[^>]*>?/gm, '')
  }

  const parser = new DOMParser()
  const doc = parser.parseFromString(rawHtml, 'text/html')

  // Disallowed dangerous elements
  const disallowedTags = [
    'script', 'iframe', 'object', 'embed', 'applet', 'meta', 'link',
    'style', 'form', 'base', 'frame', 'frameset', 'portal'
  ]

  disallowedTags.forEach(tag => {
    const elements = doc.querySelectorAll(tag)
    elements.forEach(el => el.remove())
  })

  // Sanitize all remaining elements and attributes
  const allElements = doc.body.querySelectorAll('*')
  allElements.forEach(el => {
    Array.from(el.attributes).forEach(attr => {
      const name = attr.name.toLowerCase()
      const value = attr.value.trim().toLowerCase()

      // Strip any inline JS event handlers (e.g. onerror, onload, onclick, onmouseover)
      if (name.startsWith('on')) {
        el.removeAttribute(attr.name)
      } else if (name === 'href' || name === 'src' || name === 'action') {
        // Disallow dangerous URL schemes
        if (
          value.startsWith('javascript:') ||
          value.startsWith('vbscript:') ||
          value.startsWith('data:text/html')
        ) {
          el.removeAttribute(attr.name)
        }
      }
    })
  })

  return doc.body.innerHTML
}

