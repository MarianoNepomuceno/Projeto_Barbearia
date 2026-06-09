const form = document.getElementById("formPerfil");
const mensagem = document.getElementById("mensagem");
const btnLogout = document.getElementById("btnLogout");

async function carregarPerfil() {
  const usuario = await requireCliente();
  if (!usuario) return;

  const data = await apiRequest("perfil.php");
  const perfil = data.perfil;

  document.getElementById("nome").value = perfil.nome;
  document.getElementById("email").value = perfil.email;
  document.getElementById("telefone").value = perfil.telefone || "";
}

form.addEventListener("submit", async (event) => {
  event.preventDefault();

  const senhaAtual = document.getElementById("senhaAtual").value;
  const novaSenha = document.getElementById("novaSenha").value;

  try {
    const data = await apiRequest("perfil.php", {
      method: "PUT",
      body: JSON.stringify({
        nome: document.getElementById("nome").value.trim(),
        email: document.getElementById("email").value.trim(),
        telefone: document.getElementById("telefone").value.trim(),
        senha_atual: senhaAtual,
        nova_senha: novaSenha,
      }),
    });

    mostrarMensagemAuth(mensagem, data.message, "sucesso");
    document.getElementById("senhaAtual").value = "";
    document.getElementById("novaSenha").value = "";
  } catch (error) {
    mostrarMensagemAuth(mensagem, error.message, "erro");
  }
});

btnLogout.addEventListener("click", () => logout("login.html"));
carregarPerfil();
