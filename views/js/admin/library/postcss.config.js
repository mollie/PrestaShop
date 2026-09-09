// Utilities are emitted unlayered (see src/shared/styles/globals.css), so they tie with
// other modules' unlayered utilities of the same name and load order decides; the scope
// class raises specificity so it cannot. Layered rules are left alone.
import tailwindcss from '@tailwindcss/postcss'
import autoprefixer from 'autoprefixer'

const SCOPE = '.mollie-admin-app'

// NOTE: do not add :where( here. Tailwind wraps space-y-*/space-x-*/divide-* in
// :where(...), so skipping those would leave every spacing utility unscoped; the
// base-layer :where() rules are already protected by the @layer check below.
const NEVER_PREFIX = /^(:root|html|body|\*|::?backdrop|::?file-selector-button)/i

const hasAncestor = (node, names) => {
  let p = node.parent
  while (p) {
    if (p.type === 'atrule' && names.includes(p.name)) return true
    p = p.parent
  }
  return false
}

const scopeUtilities = () => ({
  postcssPlugin: 'mollie-scope-utilities',
  Rule(rule) {
    // keyframe steps are not selectors
    if (hasAncestor(rule, ['keyframes'])) return
    if (hasAncestor(rule, ['layer'])) return

    rule.selectors = rule.selectors.map((sel) => {
      const s = sel.trim()
      if (!s || NEVER_PREFIX.test(s)) return sel
      if (s.startsWith(SCOPE)) return sel
      return `${SCOPE} ${s}`
    })
  },
})

export default {
  plugins: [tailwindcss(), scopeUtilities(), autoprefixer()],
}
