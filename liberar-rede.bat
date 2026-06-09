@echo off
echo ============================================
echo  Acesso na rede Wi-Fi - Projeto Barbearia
echo ============================================
echo.

net session >nul 2>&1
if %errorlevel% neq 0 (
    echo Execute este arquivo como Administrador:
    echo clique com o botao direito ^> Executar como administrador
    echo.
    pause
    exit /b 1
)

netsh advfirewall firewall delete rule name="XAMPP Apache HTTP (LAN)" >nul 2>&1
netsh advfirewall firewall add rule name="XAMPP Apache HTTP (LAN)" dir=in action=allow protocol=TCP localport=80 profile=private,domain

echo Regra do firewall criada na porta 80.
echo.

for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /c:"IPv4"') do (
    set IP=%%a
    goto :found
)
:found
set IP=%IP: =%

echo Seu IP na rede:
echo %IP%
echo.
echo Acesse de outro celular ou PC na mesma Wi-Fi:
echo http://%IP%/Projeto_Barbearia/pages/login.html
echo.
echo Certifique-se de que Apache e MySQL estao rodando no XAMPP.
echo.
pause
