export const useAppToast = () => {
  const toast = useToast()

  const showError = (message: string, title = 'Błąd') => {
    toast.add({
      title,
      description: message,
      color: 'error',
      duration: 5000,
    })
  }

  const showWarning = (message: string, title = 'Uwaga') => {
    toast.add({
      title,
      description: message,
      color: 'warning',
      duration: 4000,
    })
  }

  return {
    showError,
    showWarning,
  }
}
