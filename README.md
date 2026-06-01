# 📚 Sistema de Gestão de Biblioteca - Trabalho A3 PHP

![Status do Projeto](https://img.shields.io/badge/Status-Concluído-success)
![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue)
![MySQL](https://img.shields.io/badge/MySQL-Relacional-orange)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple)

Um sistema web completo para a gestão de uma biblioteca académica. O sistema permite o controlo do acervo literário, gestão de empréstimos, sistema de avaliações e diferentes níveis de privilégios de acesso.

## ✨ Funcionalidades Principais

O sistema foi concebido com base em **3 níveis de acesso** distintos:

### 1. Visitante (Não Autenticado)
* 🔍 **Busca Avançada:** Pesquisa no catálogo através de 3 filtros simultâneos (Título, Autor, Disponibilidade).
* 📖 **Leitura de Avaliações:** Consulta de críticas e notas (1 a 5 estrelas) deixadas por outros leitores.
* 📝 **Registo de Conta:** Criação autónoma de um perfil de utilizador.

### 2. Utilizador (Leitor Registado)
* 📚 **Empréstimos:** Aluguer de livros disponíveis (com cálculo automático de prazo de 7 dias).
* ⏳ **Reservas:** Inserção em fila de espera para livros atualmente alugados.
* ⭐ **Avaliações:** Possibilidade de classificar e comentar livros já lidos.
* 💡 **Sugestões:** Submissão de requisições para a compra de novos títulos.

### 3. Administrador
* ⚙️ **CRUD de Livros:** Adicionar, editar e remover obras do catálogo.
* 👥 **Gestão de Utilizadores:** Visualização de todos os membros e capacidade de **Bloquear** utilizadores com pendências ou excluir contas.
* 📄 **Relatório Gerencial (PDF):** Emissão de um relatório consolidado com indicadores globais, taxa de ocupação e destaque automático para utilizadores com dias de atraso nas devoluções.

## 🛠️ Tecnologias Utilizadas

* **Back-end:** PHP (com PDO para segurança e prevenção contra SQL Injection).
* **Base de Dados:** MySQL (Arquitetura relacional com 6 tabelas e chaves estrangeiras em cascata).
* **Front-end:** HTML5, CSS3, JavaScript.
* **Interface UI/UX:** Framework Bootstrap 5 (Design responsivo, modais interativos e alertas dinâmicos).

## 🗄️ Estrutura da Base de Dados

O modelo relacional (MER) é composto por 6 entidades:
1. `usuarios`: Controlo de acessos, passwords e status de bloqueio.
2. `livros`: Catálogo principal do acervo.
3. `emprestimos`: Registo de saídas, devoluções previstas e atrasos.
4. `avaliacoes`: Notas e comentários associados a livros específicos.
5. `reservas`: Fila de utilizadores a aguardar disponibilidade.
6. `requisicoes_novos`: Registo de sugestões de compra por parte dos leitores.

## 🚀 Como Instalar e Executar Localmente

### Pré-requisitos
* Servidor Web local (XAMPP, WampServer ou MAMP).
* PHP versão 7.4 ou superior.

### Passos de Instalação

1. **Clonar o Repositório**
   ```bash
   git clone [https://github.com/SEU-USUARIO/biblioteca-a3-php.git](https://github.com/SEU-USUARIO/biblioteca-a3-php.git)
