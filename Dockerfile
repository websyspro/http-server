FROM php:8.5-cli

# Instalar extensões necessárias
RUN docker-php-ext-install pcntl sockets

# Copiar código
COPY . /app
WORKDIR /app

# Expor porta
EXPOSE 3002

# Executar servidor
CMD ["php", "index.php"]