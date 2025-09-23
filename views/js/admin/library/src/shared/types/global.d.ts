declare module '*.css' {
  const content: string
  export default content
}

// Note: Global Window interface extensions are now in types/index.ts
// This avoids duplication and provides better type safety with imports