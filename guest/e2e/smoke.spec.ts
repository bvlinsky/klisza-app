import { test, expect } from '@playwright/test'

test('strona główna wyświetla hero tytuł', async ({ page }) => {
  await page.goto('/')
  await expect(page.getByText(/Aparat jednorazowy/)).toBeVisible()
})