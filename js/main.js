function toggleDividendInput(select) {
  var manualInput = select.nextElementSibling;
  var automaticValue = select.getAttribute("data-automatic-value");

  if (select.value === "manual") {
    manualInput.style.display = "inline-block";
    manualInput.disabled = false;
  } else if (select.value === "automatic") {
    if (!automaticValue || parseFloat(automaticValue) === 0) {
      manualInput.style.display = "inline-block";
      manualInput.disabled = true;
    } else {
      manualInput.style.display = "none";
    }
  }
}

document.addEventListener("DOMContentLoaded", function () {
  const rows = document.querySelectorAll(
    ".table tbody tr:not(.expand-content)"
  );

  rows.forEach((row) => {
    row.addEventListener("click", function () {
      const expandContent = this.nextElementSibling;
      if (expandContent && expandContent.classList.contains("expand-content")) {
        if (
          expandContent.style.display === "none" ||
          expandContent.style.display === ""
        ) {
          document
            .querySelectorAll(".expand-content")
            .forEach((content) => (content.style.display = "none"));
          expandContent.style.display = "block";
          // Ajusta a posição inicial do popup ao centro da tela
          expandContent.style.top = `${
            window.innerHeight / 2 - expandContent.offsetHeight / 2
          }px`;
          expandContent.style.left = `${
            window.innerWidth / 2 - expandContent.offsetWidth / 2
          }px`;
        } else {
          expandContent.style.display = "none";
        }
      }
    });
  });

  document.addEventListener("mousedown", function (e) {
    if (e.target.closest(".expand-content")) {
      dragItem = e.target.closest(".expand-content");
      offsetX = e.clientX - dragItem.getBoundingClientRect().left;
      offsetY = e.clientY - dragItem.getBoundingClientRect().top;
      document.addEventListener("mousemove", onMouseMove);
      document.addEventListener("mouseup", onMouseUp);
    }
  });

  function onMouseMove(e) {
    if (dragItem) {
      dragItem.style.left = `${e.clientX - offsetX}px`;
      dragItem.style.top = `${e.clientY - offsetY}px`;
    }
  }

  function onMouseUp() {
    dragItem = null;
    document.removeEventListener("mousemove", onMouseMove);
    document.removeEventListener("mouseup", onMouseUp);
  }

  window.toggleExpandContent = function (element) {
    const expandContent = element.closest(".expand-content");
    if (expandContent) {
      expandContent.style.display = "none";
    }
  };
});
