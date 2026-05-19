FROM php:8.2-apache

# Instalar extensão PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Habilitar o módulo Rewrite do Apache
RUN a2enmod rewrite

# Permitir que o .htaccess sobrescreva configurações do Apache
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copiar arquivos do projeto para o diretório web padrão do Apache
COPY . /var/www/html/

# Expor a porta 80
EXPOSE 80
