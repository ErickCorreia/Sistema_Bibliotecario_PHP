-- Criação do Banco de Dados
CREATE DATABASE IF NOT EXISTS biblioteca_a3;
USE biblioteca_a3;

-- Limpeza de tabelas anteriores (ordem inversa das chaves estrangeiras)
DROP TABLE IF EXISTS requisicoes_novos;
DROP TABLE IF EXISTS avaliacoes;
DROP TABLE IF EXISTS reservas;
DROP TABLE IF EXISTS emprestimos;
DROP TABLE IF EXISTS livros;
DROP TABLE IF EXISTS usuarios;

-- 1. TABELA: Usuarios
-- Níveis de acesso: 2 = Usuário Comum, 3 = Administrador (Visitante não logado não fica no banco)
-- Status: 'ativo' ou 'bloqueado' (para impedir novos aluguéis se houver atraso)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel_acesso INT NOT NULL DEFAULT 2, 
    status_conta ENUM('ativo', 'bloqueado') NOT NULL DEFAULT 'ativo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. TABELA: Livros
CREATE TABLE livros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    autor VARCHAR(100) NOT NULL,
    genero VARCHAR(50) NOT NULL,
    status_disponibilidade ENUM('disponivel', 'alugado') NOT NULL DEFAULT 'disponivel'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. TABELA: Emprestimos
-- Controla quem alugou o quê e os prazos
CREATE TABLE emprestimos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_livro INT NOT NULL,
    data_emprestimo DATE NOT NULL,
    data_devolucao_prevista DATE NOT NULL,
    data_devolucao_real DATE NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_livro) REFERENCES livros(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. TABELA: Avaliacoes
-- Apenas usuários logados avaliam livros que leram/alugaram
CREATE TABLE avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_livro INT NOT NULL,
    nota INT NOT NULL CHECK (nota BETWEEN 1 AND 5),
    comentario TEXT NOT NULL,
    data_avaliacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_livro) REFERENCES livros(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. TABELA: Reservas
-- Fila de espera para livros atualmente alugados
CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_livro INT NOT NULL,
    data_reserva DATE NOT NULL,
    status_reserva ENUM('aguardando', 'atendida', 'cancelada') NOT NULL DEFAULT 'aguardando',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_livro) REFERENCES livros(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. TABELA: Requisicoes_Novos
-- Sugestões de livros enviadas pelos usuários
CREATE TABLE requisicoes_novos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo_livro VARCHAR(150) NOT NULL,
    autor_livro VARCHAR(100) NOT NULL,
    justificativa TEXT NOT NULL,
    data_requisicao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==========================================
-- SCRIPT DE CARGA INICIAL (DADOS FICTÍCIOS)
-- ==========================================

-- Inserindo Usuários (Senhas em texto limpo para fins acadêmicos/testes simples)
INSERT INTO usuarios (nome, email, senha, nivel_acesso, status_conta) VALUES
('Administrador Geral', 'admin@biblioteca.com', 'admin123', 3, 'ativo'),
('Carlos Silva', 'carlos@email.com', 'user123', 2, 'ativo'),
('Ana Souza', 'ana@email.com', 'user123', 2, 'ativo'),
('Bruno Lima', 'bruno@email.com', 'user123', 2, 'bloqueado');

-- Inserindo Livros
INSERT INTO livros (titulo, autor, genero, status_disponibilidade) VALUES
('Dom Casmurro', 'Machado de Assis', 'Romance', 'disponivel'),
('O Alquimista', 'Paulo Coelho', 'Ficção', 'alugado'),
('1984', 'George Orwell', 'Distopia', 'disponivel'),
('O Senhor dos Anéis', 'J.R.R. Tolkien', 'Fantasia', 'alugado'),
('Clean Code', 'Robert C. Martin', 'Tecnologia', 'disponivel');

-- Inserindo Empréstimos
INSERT INTO emprestimos (id_usuario, id_livro, data_emprestimo, data_devolucao_prevista, data_devolucao_real) VALUES
(2, 2, '2026-05-15', '2026-05-22', '2026-05-21'), -- Já devolveu
(3, 4, '2026-05-28', '2026-06-04', NULL),        -- Ativo dentro do prazo
(4, 2, '2026-05-01', '2026-05-08', NULL);        -- Atrasado (motivo do Bruno estar bloqueado)

-- Inserindo Avaliações
INSERT INTO avaliacoes (id_usuario, id_livro, nota, comentario) VALUES
(2, 2, 5, 'Excelente leitura, uma jornada de autoconhecimento fascinante.'),
(3, 1, 4, 'Clássico indispensável, embora a leitura seja densa.');

-- Inserindo Reservas
INSERT INTO reservas (id_usuario, id_livro, data_reserva, status_reserva) VALUES
(2, 4, '2026-05-29', 'aguardando');

-- Inserindo Requisições de Novos Livros
INSERT INTO requisicoes_novos (id_usuario, titulo_livro, autor_livro, justificativa) VALUES
(3, 'O Programador Pragmático', 'Andrew Hunt', 'Livro fundamental para complementar as aulas de PHP da faculdade.');