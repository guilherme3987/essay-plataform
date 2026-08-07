#!/usr/bin/env python3
"""
Setup do banco de dados RedaçãoPro.

Lê as mesmas variáveis de ambiente usadas pelo backend PHP
(DB_HOST, DB_NAME, DB_USER, DB_PASS) e executa o script init.sql.

Dependência:
    pip install pymysql

Uso:
    python database/setup_db.py
"""
import os
import sys
import re
from pathlib import Path

try:
    import pymysql
except ImportError:
    sys.exit("Erro: pymysql não instalado. Rode 'pip install pymysql'.")

DB_HOST = os.getenv("DB_HOST", "127.0.0.1")
DB_NAME = os.getenv("DB_NAME", "redacaopro")
DB_USER = os.getenv("DB_USER", "root")
DB_PASS = os.getenv("DB_PASS", "root")

SQL_PATH = Path(__file__).resolve().parent / "init.sql"


def read_sql_statements(path: Path):
    """Lê o arquivo .sql e retorna uma lista de statements separados por ';'."""
    content = path.read_text(encoding="utf-8")
    statements = []
    for raw in content.split(";"):
        raw = re.sub(r"(?m)^\s*--.*$", "", raw).strip()
        if raw:
            statements.append(raw)
    return statements


def main():
    if not SQL_PATH.exists():
        sys.exit(f"Erro: arquivo {SQL_PATH} não encontrado.")

    statements = read_sql_statements(SQL_PATH)

    try:
        # Conecta sem selecionar banco para poder criar o database
        conn = pymysql.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASS,
            charset="utf8mb4",
            autocommit=True,
        )
        with conn.cursor() as cur:
            for sql in statements:
                cur.execute(sql)
        conn.close()
    except pymysql.err.OperationalError as e:
        sys.exit(f"Falha ao conectar no MySQL ({DB_HOST}): {e}")
    except pymysql.err.ProgrammingError as e:
        sys.exit(f"Falha ao executar SQL: {e}")

    print(f"Banco de dados '{DB_NAME}' criado/atualizado com sucesso!")


if __name__ == "__main__":
    main()
