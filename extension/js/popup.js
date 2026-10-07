document.addEventListener("DOMContentLoaded", () => {
  const searchBtn = document.getElementById("addprofilebtn");
  if (searchBtn) {
    searchBtn.addEventListener("click", () => {
      const value = document.getElementById("profileid")?.value?.trim();
      if (!value) return;
      const encoded = encodeURIComponent(value);
      chrome.tabs.create({ url: `https://www.google.com/maps/search/${encoded}` });
    });
  }
});
