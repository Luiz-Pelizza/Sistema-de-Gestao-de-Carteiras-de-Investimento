# Sistema de Gestão de Carteiras de Investimento

Este projeto foi desenvolvido para gerenciar carteiras de investimento, fornecendo funcionalidades como registro de usuários, login e exibição detalhada das ações de cada carteira. O sistema permite que os usuários acompanhem seus investimentos, ganhos/perdas e rendimentos mensais.

## Funcionalidades

- Registro de usuários
- Login de usuários
- Visualização de carteiras de investimento
- Detalhamento de ações com informações de preço de compra, preço atual, quantidade, valor total, ganhos/perdas, dividendos e rendimento mensal
- Atualização de preços de ações via API externa

## Tecnologias Utilizadas

- **Frontend:**
  - HTML5/CSS3
  - JavaScript

- **Backend:**
  - PHP

- **Banco de Dados:**
  - MySQL

## Configuração do Banco de Dados

Para configurar o banco de dados, execute os seguintes comandos SQL:

```sql
CREATE DATABASE sistema_acoes;

USE sistema_acoes;

-- Tabela para os usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Tabela para as ações
CREATE TABLE acoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    purchase_price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL,
    dividend_value DECIMAL(10, 2) DEFAULT 0,
    dividend_type varchar(10) DEFAULT 'automatic'
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE

);
