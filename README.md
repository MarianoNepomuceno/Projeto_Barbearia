# Sistema de Gestão para Barbearia

> Projeto desenvolvido para a disciplina de Prática Profissional Supervisionada.

## Sobre o Projeto

Sistema de agendamento e controle administrativo para barbearias, com autenticação de clientes e administrador, backend em **PHP**, banco **MySQL** e frontend em HTML/CSS/JavaScript.

---

## Funcionalidades

### Cliente

- Cadastro de conta (`cadastro.html`);
- Login unificado (`login.html`) — clientes e administrador;
- Agendamento de horários com escolha de serviços;
- Cálculo automático do total;
- Comprovante com opção de impressão.

### Administrador

- Acesso via a mesma página de login (redireciona para o painel);
- Atualização de preços dos serviços;
- Bloqueio e liberação de horários;
- Visualização da agenda completa;
- Cancelamento de agendamentos.

---

## Tecnologias

- HTML5, CSS3, JavaScript
- PHP 8+
- MySQL
- Sessões PHP para autenticação

---

## Requisitos

- [XAMPP](https://www.apachefriends.org/) ou ambiente com Apache + PHP + MySQL
- PHP 8.0 ou superior
- MySQL 5.7+ ou MariaDB

---

## Instalação

### 1. Copiar o projeto

Coloque a pasta do projeto em `C:\xampp\htdocs\Projeto_Barbearia` (ou equivalente no seu servidor).

### 2. Configurar o banco

Copie o arquivo de exemplo e ajuste se necessário:

```bash
copy api\config\database.example.php api\config\database.php
```

Valores padrão do XAMPP:

- Host: `localhost`
- Usuário: `root`
- Senha: vazia
- Banco: `barbearia`

### 3. Criar tabelas e admin padrão

Inicie o Apache e o MySQL no XAMPP e execute:

```bash
php install.php
```

Ou acesse no navegador: `http://localhost/Projeto_Barbearia/install.php`

### 4. Acessar o sistema

- Login: `http://localhost/Projeto_Barbearia/login.php`
- Agendamento: `http://localhost/Projeto_Barbearia/index.html`
- Cadastro: `http://localhost/Projeto_Barbearia/pages/cadastro.html`

**Admin padrão:**

- E-mail: `admin@barbearia.com`
- Senha: `admin123`

> Altere a senha do administrador após a primeira instalação.

---

## Estrutura do Projeto

```bash
Projeto_Barbearia
│
├── api/
│   ├── auth/
│   │   ├── login.php
│   │   ├── register.php
│   │   ├── logout.php
│   │   └── me.php
│   │
│   ├── config/
│   │   └── database.php
│   │
│   ├── includes/
│   │   ├── bootstrap.php
│   │   └── auth.php
│   │
│   ├── agendamentos.php
│   ├── bloqueios.php
│   ├── clientes.php
│   ├── disponibilidade.php
│   ├── perfil.php
│   └── precos.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   │
│   ├── js/
│   │   ├── admin.js
│   │   ├── auth.js
│   │   ├── perfil.js
│   │   └── script.js
│   │
│   └── img/
│
├── database/
│   └── schema.sql
│
├── pages/
│   ├── admin-login.html
│   ├── admin.html
│   ├── cadastro.html
│   ├── login.html
│   └── perfil.html
│
├── .gitignore
├── index.html
├── install.php
├── liberar-rede.bat
├── login.php
└── README.md
```

---

## API (resumo)

| Endpoint | Método | Acesso | Descrição |
|----------|--------|--------|-----------|
| `api/auth/register.php` | POST | Público | Cadastro de cliente |
| `api/auth/login.php` | POST | Público | Login (cliente ou admin) |
| `api/auth/logout.php` | POST | Autenticado | Encerrar sessão |
| `api/auth/me.php` | GET | Autenticado | Usuário logado |
| `api/precos.php` | GET | Público | Listar preços |
| `api/precos.php` | PUT | Admin | Atualizar preços |
| `api/agendamentos.php` | GET | Autenticado | Listar agendamentos |
| `api/agendamentos.php` | POST | Cliente | Criar agendamento |
| `api/agendamentos.php` | DELETE | Cliente/Admin | Cancelar agendamento |
| `api/bloqueios.php` | GET | Autenticado | Listar bloqueios |
| `api/bloqueios.php` | POST | Admin | Bloquear horário |
| `api/bloqueios.php` | DELETE | Admin | Liberar horário |
| `api/disponibilidade.php` | GET | Autenticado | Horários ocupados/bloqueados |

---

## Acesso por outros dispositivos (mesma Wi-Fi)

1. Mantenha **Apache** e **MySQL** rodando no XAMPP neste computador.
2. Execute `liberar-rede.bat` **como administrador** (clique direito → Executar como administrador) para liberar a porta 80 no firewall.
3. No celular ou outro PC conectado à **mesma rede Wi-Fi**, acesse:

```
http://SEU_IP/Projeto_Barbearia/login.php
```

Exemplo: `http://xxx.xxx.x.x/Projeto_Barbearia/login.php`

Para descobrir o IP: no Windows, abra o Prompt de Comando e digite `ipconfig` (use o endereço **IPv4** da rede Wi-Fi).

> O PC com o XAMPP precisa estar ligado e na mesma rede que os outros dispositivos.

## Observações

- O projeto precisa ser executado via servidor web (Apache/PHP). Abrir os arquivos HTML diretamente no navegador (`file://`) não funcionará com a API.
- Após a instalação, remova ou proteja o `install.php` em ambiente de produção.
