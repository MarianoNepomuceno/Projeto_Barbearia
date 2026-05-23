// ELEMENTOS
const form = document.getElementById("formAgendamento");
const totalSpan = document.getElementById("total");
const selectHora = document.getElementById("hora");
const mensagem = document.getElementById("mensagem");
const comprovante = document.getElementById("comprovante");
const comprovanteConteudo = document.getElementById("comprovanteConteudo");
const inputData = document.getElementById("data");

const barba = document.getElementById("barba");
const sobrancelha = document.getElementById("sobrancelha");

// DADOS
let agendamentos = JSON.parse(localStorage.getItem("agendamentos")) || [];
let bloqueios = JSON.parse(localStorage.getItem("bloqueios")) || [];

let precos = JSON.parse(localStorage.getItem("precos")) || {
  "Degradê": 30,
  "Social": 25,
  "Tesoura": 35,
  "Barba": 20,
  "Sobrancelha": 10
};

// MOSTRAR PREÇOS
function mostrarPrecos() {
  if (window.precoDegrade) precoDegrade.textContent = precos["Degradê"].toFixed(2);
  if (window.precoSocial) precoSocial.textContent = precos["Social"].toFixed(2);
  if (window.precoTesoura) precoTesoura.textContent = precos["Tesoura"].toFixed(2);
  if (window.precoBarba) precoBarba.textContent = precos["Barba"].toFixed(2);
  if (window.precoSobrancelha) precoSobrancelha.textContent = precos["Sobrancelha"].toFixed(2);
}

mostrarPrecos();

// GERAR HORÁRIOS
function gerarHorarios() {
  if (!selectHora) return;

  selectHora.innerHTML = '<option value="">Selecione o horário</option>';

  const inicio = 9 * 60;
  const fim = 18 * 60;

  for (let m = inicio; m <= fim; m += 30) {
    if (m >= 12 * 60 && m < 13 * 60 + 30) continue;

    const h = String(Math.floor(m / 60)).padStart(2, "0");
    const min = String(m % 60).padStart(2, "0");
    const hora = `${h}:${min}`;

    const opt = document.createElement("option");
    opt.value = hora;
    opt.textContent = hora;

    selectHora.appendChild(opt);
  }
}

// ATUALIZAR HORÁRIOS
function atualizarHorariosDisponiveis() {
  const data = inputData.value;
  if (!data) return;

  agendamentos = JSON.parse(localStorage.getItem("agendamentos")) || [];
  bloqueios = JSON.parse(localStorage.getItem("bloqueios")) || [];

  const ocupados = agendamentos
    .filter(a => a.data === data)
    .map(a => a.hora);

  const bloqueados = bloqueios
    .filter(b => b.data === data)
    .map(b => b.hora);

  [...selectHora.options].forEach(opt => {
    if (!opt.value) return;

    if (ocupados.includes(opt.value) || bloqueados.includes(opt.value)) {
      opt.disabled = true;
      opt.textContent = `${opt.value} (Indisponível)`;
    }
  });
}

// EVENTO DATA
inputData.addEventListener("change", () => {
  gerarHorarios();
  atualizarHorariosDisponiveis();
});

// CALCULAR TOTAL
function calcularTotal() {
  let total = 0;

  const corte = document.querySelector('input[name="corte"]:checked');
  if (corte) total += precos[corte.value];

  if (barba && barba.checked) total += precos["Barba"];
  if (sobrancelha && sobrancelha.checked) total += precos["Sobrancelha"];

  totalSpan.textContent = total.toFixed(2);
}

// EVENTOS DOS INPUTS CORRETOS
document.querySelectorAll('input[name="corte"], #barba, #sobrancelha')
  .forEach(el => el.addEventListener("change", calcularTotal));

// INICIAL
gerarHorarios();

// SUBMIT
form.addEventListener("submit", e => {
  e.preventDefault();

  const nome = document.getElementById("nome").value.trim();
  const telefone = document.getElementById("telefone").value.trim();
  const data = inputData.value;
  const hora = selectHora.value;

  if (!hora) {
    mostrarMensagem("Selecione um horário válido.", "erro");
    return;
  }

  // ATUALIZA DADOS ANTES DE VALIDAR
  agendamentos = JSON.parse(localStorage.getItem("agendamentos")) || [];
  bloqueios = JSON.parse(localStorage.getItem("bloqueios")) || [];

  const conflito = agendamentos.some(a => a.data === data && a.hora === hora) ||
                   bloqueios.some(b => b.data === data && b.hora === hora);

  if (conflito) {
    mostrarMensagem("Esse horário já está ocupado ou bloqueado.", "erro");
    return;
  }

  const corte = document.querySelector('input[name="corte"]:checked');
  const servicos = [];

  if (corte) servicos.push(corte.value);
  if (barba && barba.checked) servicos.push("Barba");
  if (sobrancelha && sobrancelha.checked) servicos.push("Sobrancelha");

  const total = totalSpan.textContent;

  agendamentos.push({ nome, telefone, data, hora, servicos, total });
  localStorage.setItem("agendamentos", JSON.stringify(agendamentos));

  mostrarMensagem("Agendamento realizado com sucesso!", "sucesso");
  
  //COMPROVANTE
  comprovanteConteudo.innerHTML = `
  <p>Cliente: ${nome}</p>
  <p>WhatsApp: ${telefone}</p>
  <p>Data: ${data}</p>
  <p>Hora: ${hora}</p>
  <hr>
  <p>Serviços:</p>
  <p>${servicos.join(", ")}</p>
  <hr>
  <p><strong>Total: R$ ${total}</strong></p>
`;

  comprovante.style.display = "block";

  form.reset();
  totalSpan.textContent = "0.00";

  gerarHorarios();
  atualizarHorariosDisponiveis();
});

// MENSAGEM PADRONIZADA
function mostrarMensagem(texto, tipo) {
  mensagem.textContent = texto;
  mensagem.className = `mensagem ${tipo}`;
  mensagem.style.display = "block";
}
function imprimirComprovante() {
  window.print();
}