let usuarioAdmin = null;
let agendamentos = [];
let bloqueios = [];
let precos = {};

const lista = document.getElementById("listaAgendamentos");
const listaClientes = document.getElementById("listaClientes");
const bloqueioHora = document.getElementById("bloqueioHora");
const bloqueioData = document.getElementById("bloqueioData");
const adminInfo = document.getElementById("adminInfo");
const btnLogout = document.getElementById("btnLogout");

function gerarHorariosAdmin() {
  bloqueioHora.innerHTML = '<option value="">Selecione o horário</option>';

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
    bloqueioHora.appendChild(opt);
  }
}

async function carregarPrecos() {
  const data = await apiRequest("precos.php");
  precos = data.precos;
  carregarPrecosNoFormulario();
}

function carregarPrecosNoFormulario() {
  precoDegrade.value = precos["Degradê"];
  precoSocial.value = precos["Social"];
  precoTesoura.value = precos["Tesoura"];
  precoBarba.value = precos["Barba"];
  precoSobrancelha.value = precos["Sobrancelha"];
}

async function salvarPrecos() {
  try {
    precos = {
      "Degradê": Number(precoDegrade.value),
      "Social": Number(precoSocial.value),
      "Tesoura": Number(precoTesoura.value),
      "Barba": Number(precoBarba.value),
      "Sobrancelha": Number(precoSobrancelha.value),
    };

    await apiRequest("precos.php", {
      method: "PUT",
      body: JSON.stringify({ precos }),
    });

    alert("Preços salvos!");
  } catch (error) {
    alert(error.message);
  }
}

async function bloquearHorario() {
  const data = bloqueioData.value;
  const hora = bloqueioHora.value;

  if (!data || !hora) {
    alert("Selecione data e horário.");
    return;
  }

  try {
    await apiRequest("bloqueios.php", {
      method: "POST",
      body: JSON.stringify({ data, hora }),
    });

    alert("Horário bloqueado!");
    await listar();
  } catch (error) {
    alert(error.message);
  }
}

async function listar() {
  const [agendamentosData, bloqueiosData] = await Promise.all([
    apiRequest("agendamentos.php"),
    apiRequest("bloqueios.php"),
  ]);

  agendamentos = agendamentosData.agendamentos;
  bloqueios = bloqueiosData.bloqueios;

  lista.innerHTML = "";

  if (agendamentos.length === 0 && bloqueios.length === 0) {
    lista.innerHTML = "<li>Nenhum registro.</li>";
    return;
  }

  agendamentos.forEach((agendamento) => {
    const li = document.createElement("li");

    li.innerHTML = `
      <strong>${escapeHtml(agendamento.nome)}</strong> - ${escapeHtml(agendamento.telefone)}<br>
      📅 ${escapeHtml(agendamento.data)} às ${escapeHtml(agendamento.hora)}<br>
      💈 ${escapeHtml(agendamento.servicos.join(", "))}<br>
      💰 R$ ${escapeHtml(agendamento.total)}
      <button type="button" data-cancelar="${agendamento.id}">Cancelar</button>
    `;

    lista.appendChild(li);
  });

  bloqueios.forEach((bloqueio) => {
    const li = document.createElement("li");

    li.innerHTML = `
      🔒 <strong>Horário bloqueado</strong><br>
      📅 ${escapeHtml(bloqueio.data)} às ${escapeHtml(bloqueio.hora)}
      <button type="button" data-liberar="${bloqueio.id}">Liberar</button>
    `;

    lista.appendChild(li);
  });

  lista.querySelectorAll("[data-cancelar]").forEach((botao) => {
    botao.addEventListener("click", async () => {
      if (!confirm("Cancelar agendamento?")) return;

      try {
        await apiRequest(`agendamentos.php?id=${botao.dataset.cancelar}`, {
          method: "DELETE",
        });
        await listar();
      } catch (error) {
        alert(error.message);
      }
    });
  });

  lista.querySelectorAll("[data-liberar]").forEach((botao) => {
    botao.addEventListener("click", async () => {
      if (!confirm("Liberar horário?")) return;

      try {
        await apiRequest(`bloqueios.php?id=${botao.dataset.liberar}`, {
          method: "DELETE",
        });
        await listar();
      } catch (error) {
        alert(error.message);
      }
    });
  });
}

function formatarData(dataIso) {
  if (!dataIso) return "-";
  return new Date(dataIso).toLocaleDateString("pt-BR");
}

async function listarClientes() {
  const data = await apiRequest("clientes.php");
  const clientes = data.clientes;

  listaClientes.innerHTML = "";

  if (clientes.length === 0) {
    listaClientes.innerHTML = "<li>Nenhum cliente cadastrado.</li>";
    return;
  }

  clientes.forEach((cliente) => {
    const li = document.createElement("li");

    li.innerHTML = `
      <strong>${escapeHtml(cliente.nome)}</strong><br>
      📧 ${escapeHtml(cliente.email)}<br>
      📱 ${escapeHtml(cliente.telefone || "Não informado")}<br>
      📅 Cadastro: ${escapeHtml(formatarData(cliente.criado_em))}
      <button type="button" class="btn-perigo" data-excluir-cliente="${cliente.id}">Excluir conta</button>
    `;

    listaClientes.appendChild(li);
  });

  listaClientes.querySelectorAll("[data-excluir-cliente]").forEach((botao) => {
    botao.addEventListener("click", async () => {
      if (!confirm("Excluir esta conta? O cliente poderá se cadastrar novamente.")) return;

      try {
        await apiRequest(`clientes.php?id=${botao.dataset.excluirCliente}`, {
          method: "DELETE",
        });
        await listarClientes();
        await listar();
      } catch (error) {
        alert(error.message);
      }
    });
  });
}

async function inicializarAdmin() {
  usuarioAdmin = await requireAdmin();

  if (!usuarioAdmin) return;

  adminInfo.textContent = `Administrador: ${usuarioAdmin.nome}`;
  btnLogout.addEventListener("click", () => logout("login.html"));

  gerarHorariosAdmin();
  await carregarPrecos();
  await listarClientes();
  await listar();
}

inicializarAdmin();
