// Scope Tailwind's utilities to the module's own React roots.
//
// Why: utilities are emitted UNLAYERED (see src/shared/styles/globals.css) so they
// outrank other modules' unlayered global resets. But that leaves them tying with
// other modules' unlayered utilities of the same name - PrestaShop 9.2's bundled
// ps_ask_ai ships 636 of them, 92 of which collide with ours - and ties go to
// whichever stylesheet loads last. Prefixing with a scope class raises our
// specificity to (0,2,0), which wins regardless of load order.
//
// Only unlayered rules are prefixed. That is exactly the utilities: theme lives in
// @layer theme, preflight in @layer base, and Tailwind's @property fallbacks in
// @layer properties, so all of those keep working page-wide and are left alone.
import tailwindcss from '@tailwindcss/postcss'
import autoprefixer from 'autoprefixer'

const SCOPE = '.mollie-admin-app'

// Selectors that must never be prefixed even when unlayered: our own design tokens
// and anything that has to stay global.
// NOTE: do not add :where( here. Tailwind wraps space-y-*/space-x-*/divide-* in
// :where(...), and skipping those leaves every spacing utility unscoped, so it keeps
// losing to other modules' utilities. The base-layer :where() rules that need to stay
// global are already protected by the @layer check below.
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
    // keyframe steps (from/to/50%) are not selectors
    if (hasAncestor(rule, ['keyframes'])) return
    // layered rules (theme, base, properties) stay global
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
