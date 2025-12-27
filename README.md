# Objetivo desse projeto
# - Criar um servidor, para substituir o apache como servidor http(s)
# - Criando um socket em uma determinda porta definida pelo arquivo .ENV

# Situação atual
# - Falta pegar o payload enviado pelo cliente
# 
# - - Por JSON -> OM
# - - Por FormData -> OM
# - - Por FormUrlEncoded -> OM
# - - Por Query -> OM
# - - Por Params -> OM

# Como usar
# - $httpClient = new HttpClient();
# - $httpClient->listen( 3002 );