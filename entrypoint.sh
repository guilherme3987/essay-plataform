#!/bin/bash

# Garante que as pastas existam
mkdir -p /var/www/html/api/uploads/redacoes
mkdir -p /var/www/html/api/uploads/themes

# Força o usuário do Apache (www-data) a ser o dono dessas pastas
chown -R www-data:www-data /var/www/html/api/uploads

# Dá permissão total de escrita e leitura
chmod -R 777 /var/www/html/api/uploads

# Inicia o servidor Apache (comportamento padrão da imagem do PHP)
exec apache2-foreground