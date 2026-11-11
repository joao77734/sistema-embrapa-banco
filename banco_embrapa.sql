CREATE DATABASE IF NOT EXISTS sistema_embrapa_pi;

USE sistema_embrapa_pi;
 
CREATE TABLE Setor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL, status BOOLEAN NOT NULL DEFAULT TRUE
);
 
CREATE TABLE Pesquisador (

    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    matricula VARCHAR(20) NOT NULL UNIQUE,
    funcao VARCHAR(13) NOT NULL,
    senha_hash VARCHAR(255) NULL,
    id_setor INT NOT NULL,
    score INT DEFAULT 0,
    status BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (id_setor) REFERENCES Setor(id)
);
 
CREATE TABLE Validador (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    matricula VARCHAR(20) NOT NULL UNIQUE,
    funcao VARCHAR(13) NOT NULL,
    senha_hash VARCHAR(255) NULL,
    id_setor INT NOT NULL,
    status BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (id_setor) REFERENCES Setor(id)
);
 
CREATE TABLE Ano (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ano YEAR NOT NULL UNIQUE,
    status BOOLEAN NOT NULL DEFAULT TRUE
);
 
CREATE TABLE Acao_pesquisador (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    descricao VARCHAR(300) NOT NULL,
    categoria_acao VARCHAR(100) NOT NULL,
    data_realizacao DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Pendente',
    id_pesquisador INT NOT NULL,
    FOREIGN KEY (id_pesquisador) REFERENCES Pesquisador(id)
);
 
CREATE TABLE Ano_acao (
    id_ano INT NOT NULL,
    id_acao INT NOT NULL,
    peso INT NOT NULL,
    limite VARCHAR(255),
    PRIMARY KEY (id_ano, id_acao),
    FOREIGN KEY (id_ano) REFERENCES Ano(id),
    FOREIGN KEY (id_acao) REFERENCES Acao_pesquisador(id) ON DELETE CASCADE
);
 
CREATE TABLE Administrador (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    funcao VARCHAR(13) NOT NULL,
    senha_hash VARCHAR(255) NULL,
    status BOOLEAN NOT NULL DEFAULT TRUE
);
 
CREATE TABLE Log_auditor (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    operacao_tabela VARCHAR(150) NOT NULL,
    id_registro INT NOT NULL,
    data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    matricula_usuario VARCHAR(20) NOT NULL
);

CREATE TABLE IF NOT EXISTS Usuarios_conta (
    email VARCHAR(100) PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo VARCHAR(20) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    status BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO Setor (nome)
SELECT 'SIPT' WHERE NOT EXISTS (
    SELECT 1 FROM Setor WHERE nome = 'SIPT' AND status = TRUE
);

INSERT INTO Setor (nome)
SELECT 'NCO' WHERE NOT EXISTS (
    SELECT 1 FROM Setor WHERE nome = 'NCO' AND status = TRUE
);

INSERT INTO Setor (nome)
SELECT 'SPAT' WHERE NOT EXISTS (
    SELECT 1 FROM Setor WHERE nome = 'SPAT' AND status = TRUE
);

select *  from Setor;

DELETE FROM Setor WHERE id = 4;

UPDATE Setor SET nome = 'SPAT' WHERE id = 3;