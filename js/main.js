function toggleDividendInput(selectElement) {
  var inputElement = selectElement.nextElementSibling;
  var form = selectElement.closest('form');
  var dividendValue = inputElement.value;

  if (selectElement.value === 'manual') {
      inputElement.style.display = 'inline-block';
  } else {
      inputElement.style.display = '';
      dividendValue = 0;
  }

  var updateData = new FormData(form);
  updateData.append('dividend_type', selectElement.value);
  updateData.append('dividend_value', dividendValue);

  fetch('update_dividend.php', {
      method: 'POST',
      body: updateData
  })
  .then(response => response.text())
  .then(data => {
      console.log(data);
  })
  .catch(error => {
      console.error('Error:', error);
  });
}

document.addEventListener("DOMContentLoaded", function () {
  const rows = document.querySelectorAll(".table tbody tr:not(.expand-content)");

  rows.forEach((row) => {
    row.addEventListener("click", function () {
      const expandContent = this.nextElementSibling;
      if (expandContent && expandContent.classList.contains("expand-content")) {
        if (
          expandContent.style.display === "none" ||
          expandContent.style.display === ""
        ) {
          expandContent.style.display = "block";
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

  let dragItem = null;
  let offsetX = 0;
  let offsetY = 0;

  document.addEventListener("mousedown", function (e) {
    if (e.target.closest(".expand-content") && !e.target.closest(".expand-header")) {
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

function generatePDF(username) {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();

  const userRow = document.querySelector(`tr[data-username="${username}"]`);
  if (!userRow) {
      console.error('Usuário não encontrado:', username);
      return;
  }

  const expandContent = userRow.nextElementSibling.querySelector('.expand-content table');
  if (!expandContent) {
      console.error('Conteúdo expandido não encontrado para o usuário:', username);
      return;
  }

  let yPosition = 10;
  doc.text(`Resumo da Carteira: ${username}`, 10, yPosition);
  yPosition += 10;

  const headers = ['Ativo', 'Preço de Compra', 'Preço Atual', 'Quantidade', 'Valor Total', 'P&L', 'Dividendo', 'Total Dividendos', 'Rendimento Mensal'];
  const data = [...expandContent.querySelectorAll('tbody tr')].map(tr => {
      return [...tr.querySelectorAll('td')].map(td => td.textContent);
  });

  doc.autoTable({
      startY: yPosition,
      head: [headers],
      body: data
  });

  yPosition = doc.autoTable.previous.finalY + 10;

  // Adiciona valores de Total Investido, P&L Total e Total Rendimento Mensal
  const totalInvestedElem = userRow.querySelector('[data-total-invested]');
  const totalPnLElem = userRow.querySelector('[data-total-pnl]');
  const totalMonthlyYieldElem = userRow.querySelector('[data-total-monthly-yield]');

  if (totalInvestedElem && totalPnLElem && totalMonthlyYieldElem) {
      const totalInvested = totalInvestedElem.textContent;
      const totalPnL = totalPnLElem.textContent;
      const totalMonthlyYield = totalMonthlyYieldElem.textContent;

      doc.text(`Total Investido: ${totalInvested}`, 10, yPosition);
      yPosition += 10;
      doc.text(`P&L Total: ${totalPnL}`, 10, yPosition);
      yPosition += 10;
      doc.text(`Total Rendimento Mensal: ${totalMonthlyYield}`, 10, yPosition);
  } else {
      console.error('Elementos totais não encontrados para o usuário:', username);
  }

  doc.save(`Resumo_Carteira_${username}.pdf`);
}
