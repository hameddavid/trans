import { ref } from 'vue';

export function usePagination(fetchFn, defaultPageSize = 10) {
  const currentPage = ref(1);
  const totalPages = ref(1);
  const totalItems = ref(0);
  const pageSize = ref(defaultPageSize);

  async function goToPage(page) {
    if (page < 1 || page > totalPages.value) return;
    currentPage.value = page;
    const result = await fetchFn(page, pageSize.value);
    if (result) {
      totalPages.value = result.totalPages ?? result.last_page ?? 1;
      totalItems.value = result.totalItems ?? result.total ?? 0;
    }
  }

  function nextPage() {
    if (currentPage.value < totalPages.value) {
      goToPage(currentPage.value + 1);
    }
  }

  function prevPage() {
    if (currentPage.value > 1) {
      goToPage(currentPage.value - 1);
    }
  }

  return {
    currentPage,
    totalPages,
    totalItems,
    pageSize,
    goToPage,
    nextPage,
    prevPage,
  };
}
