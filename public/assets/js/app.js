(() => {
  const modal = document.querySelector(".modal");
  if (modal instanceof HTMLDialogElement && !modal.open) {
    modal.showModal();
    modal.querySelector("input:not([type=hidden]), select")?.focus();
  }

  const sidebar = document.querySelector("#sidebar");
  const closeMenu = () => {
    sidebar?.classList.remove("sidebar--open");
    document.body.classList.remove("menu-open");
  };

  document.querySelector("[data-menu-toggle]")?.addEventListener("click", () => {
    sidebar?.classList.toggle("sidebar--open");
    document.body.classList.toggle("menu-open");
  });
  document.querySelector("[data-menu-close]")?.addEventListener("click", closeMenu);

  document.querySelectorAll("[data-notice-close]").forEach((button) => {
    button.addEventListener("click", () => button.closest(".notice")?.remove());
  });

  document.querySelectorAll("form[data-confirm]").forEach((form) => {
    form.addEventListener("submit", (event) => {
      if (!window.confirm(form.getAttribute("data-confirm") || "Confirmar esta ação?")) {
        event.preventDefault();
      }
    });
  });

  const clientSelect = document.querySelector("[data-client-select]");
  const petSelect = document.querySelector("[data-pet-select]");
  const updatePets = () => {
    if (!clientSelect || !petSelect) return;
    const selectedClient = clientSelect.value;
    [...petSelect.options].forEach((option) => {
      if (!option.value) return;
      const visible = option.dataset.client === selectedClient;
      option.hidden = !visible;
      option.disabled = !visible;
      if (!visible && option.selected) petSelect.value = "";
    });
  };
  clientSelect?.addEventListener("change", updatePets);
  updatePets();

  const productSelect = document.querySelector("[data-sale-product]");
  const quantityInput = document.querySelector("[data-sale-quantity]");
  const total = document.querySelector("[data-sale-total]");
  const updateSaleTotal = () => {
    if (!productSelect || !quantityInput || !total) return;
    const selected = productSelect.selectedOptions[0];
    const price = Number(selected?.dataset.price || 0);
    const stock = Number(selected?.dataset.stock || 0);
    const quantity = Math.max(0, Number(quantityInput.value || 0));
    quantityInput.max = String(stock);
    total.textContent = new Intl.NumberFormat("pt-BR", {
      style: "currency",
      currency: "BRL",
    }).format(price * quantity);
  };
  productSelect?.addEventListener("change", updateSaleTotal);
  quantityInput?.addEventListener("input", updateSaleTotal);
  updateSaleTotal();

  document.querySelector("[data-date-navigation]")?.addEventListener("change", (event) => {
    const date = event.currentTarget.value;
    if (date) window.location.href = `?page=schedule&date=${encodeURIComponent(date)}`;
  });
})();
