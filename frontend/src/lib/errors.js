/** Readable message from an Axios/Laravel error response. */
export function getErrorMessage(err, fallback = 'Something went wrong.') {
  const errors = err?.response?.data?.errors
  if (errors && typeof errors === 'object') {
    const messages = Object.values(errors).flat().filter(Boolean)
    if (messages.length) return messages.join(' ')
  }

  return err?.response?.data?.message || fallback
}
