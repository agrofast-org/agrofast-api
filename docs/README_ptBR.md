# Manipulador de Requisições HTTP em PHP e Nginx
Este repositório GitHub fornece uma configuração abrangente de servidor Nginx otimizada para aplicativos PHP, especificamente adaptada para implantação de APIs da web. Inclui um modelo pronto para uso projetado para agilizar o processo de desenvolvimento e implantação na plataforma Railway. Esta configuração é ideal para desenvolvedores que buscam lançar APIs baseadas em PHP com Nginx de forma rápida e eficiente, garantindo compatibilidade e desempenho em implantações Railway.

# Dependências
Este modelo pronto tem algumas dependências que precisam ser instaladas no seu ambiente de trabalho:
- **Docker**: Principalmente usado para testar a aplicação
- **PHP 8.1**: A linguagem de programação usada
- **Composer**: Gerenciador de pacotes do PHP

# 1º Passo: Nome do domínio
Em sua maquina de trabalho, adicione a seguinte linha de instrução no arquivo `/etc/hosts`:

```
127.0.0.1   choir.api.com
```

# 2º Passo: Preparando o ambiente
Construir a sua imagem Docker personalizada executando: `./docker/build.sh`

### Exceções
- Em caso de problemas com permissão, use `sudo` para executar os arquivos

# 3º Passo: Rodando o Docker
`docker-compose up`: Versão standalone

`docker-compose up -d`: Versão daemon

### Exceptions
- Se o **daemon** não está permitindo o uso da porta `0.0.0.0:80`, mude o arquivo `docker-compose.yml` para que ele exponha a porta 81:
```yml
    expose:
      - 3000
      - 81
    ports:
      - 3000:3000
      - 81:81
```

# 4º Passo: Cheque seu navegador
Abra http://choir.api.com/, você deve ver a seguinte entrada: `ping: "pong"`.

# Studying how it works
Os scripts mais importante são:
- `docker/nginx/Dockerfile`: isso compila a sua imagem Docker. Aqui voce pode encontrar os pacotes instalados no Linux para fazer sua aplicação funcionar.

- `docker/nginx/start.sh`: Esse é o script que é executado quando o contêiner é ativado.

- `docker/apply-config.sh`: Apenas um atalho para aplicar as suas mudanças no arquivo de configuração do Nginx. Você deve rodar esse script diretamente do seu contêiner.

- `docker/nginx/ssh.sh`: Uma forma fácil de entrar no seu contêiner usando SSH. Isso permite que você execute experimentos de teste na sua aplicação.

- `config/nginx`: Aqui todos os arquivos de configuração do Nginx ficam e podem ser editados conforme suas necessidades.

- `config/php8`: Aqui ficam todos os arquivos de configuração do PHP FPM e podem ser editados conformes suas necessidades.