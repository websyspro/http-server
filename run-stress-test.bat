@echo off
echo Iniciando teste de stress com 10 processos simultaneos...
echo Cada processo fara 10.000 requisicoes = 100.000 total

start "Test 1" cmd /k "node test.js"
start "Test 2" cmd /k "node test.js"
start "Test 3" cmd /k "node test.js"
start "Test 4" cmd /k "node test.js"
start "Test 5" cmd /k "node test.js"
start "Test 6" cmd /k "node test.js"
start "Test 7" cmd /k "node test.js"
start "Test 8" cmd /k "node test.js"
start "Test 9" cmd /k "node test.js"
start "Test 10" cmd /k "node test.js"

echo Todos os 10 processos foram iniciados!
pause