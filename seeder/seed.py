#!/usr/bin/env python3
"""Seed script for RedaçãoPro database.

Populates the database with test users:
- admin@redacaopro.local (admin)
- professor@redacaopro.local (teacher)  
- aluno@redacaopro.local (student)

Password for all users: Password@123
"""

import os
import sys

try:
    import pymysql
    from pymysql import Error
except ImportError:
    print("❌ pymysql não instalado. Instale com: pip install pymysql")
    sys.exit(1)

DB_CONFIG = {
    'host': os.getenv('DB_HOST', '127.0.0.1'),
    'port': int(os.getenv('DB_PORT', '3306')),
    'user': os.getenv('DB_USER', 'root'),
    'password': os.getenv('DB_PASS', 'root'),
    'database': os.getenv('DB_NAME', 'redacaopro'),
    'charset': 'utf8mb4',
}

SAMPLE_USERS = [
    {
        'name': 'Administrador RedaçãoPro',
        'email': 'admin@redacaopro.local',
        'role': 'admin',
        'password': 'Password@123',
        'hash': '$2y$10$FB7Qf5c9NwVtgj6Gr5P0VOW7VgYqWJuGhP/qy8..XUjSmUhRnpVzK'
    },
    {
        'name': 'Professor RedaçãoPro',
        'email': 'professor@redacaopro.local',
        'role': 'teacher',
        'password': 'Password@123',
        'hash': '$2y$10$65rpyxQrHCBcjEtp4TdutehCtInmtR3IMXYJgeUCI//9xhwK.k2mq'
    },
    {
        'name': 'Aluno RedaçãoPro',
        'email': 'aluno@redacaopro.local',
        'role': 'student',
        'password': 'Password@123',
        'hash': '$2y$10$TCHndByo.70LwaEHY09dcuMCSRek7jRh9GXvhbBAXqdB1U7aGNfP6'
    }
]

def validate_email(email: str) -> bool:
    return '@' in email and '.' in email and len(email) > 5

def main():
    print("=" * 60)
    print("RedaçãoPro - Database Seeder")
    print("=" * 60)
    print(f"Banco: {DB_CONFIG['database']}")
    print(f"Host: {DB_CONFIG['host']}")
    print(f"Usuários a processar: {len(SAMPLE_USERS)}")
    print()

    try:
        conn = pymysql.connect(**DB_CONFIG)
        print("✅ Conectado ao banco de dados")
        
        cursor = conn.cursor()
        cursor.execute(f"CREATE DATABASE IF NOT EXISTS {DB_CONFIG['database']}")
        conn.select_db(DB_CONFIG['database'])
        
        for user in SAMPLE_USERS:
            if not validate_email(user['email']):
                print(f"❌ E-mail inválido para {user['name']}")
                continue
                
            sql = """
                INSERT INTO users (name, email, password, role, must_change_password)
                VALUES (%s, %s, %s, %s, 0)
                ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    password = VALUES(password),
                    role = VALUES(role)
            """
            
            cursor.execute(sql, (
                user['name'],
                user['email'],
                user['hash'],
                user['role']
            ))
            
            print(f"✅ Processado: {user['name']} ({user['email']}) - {user['role']}")
        
        conn.commit()
        
        cursor.execute("SELECT id, name, email, role FROM users ORDER BY id")
        users = cursor.fetchall()
        
        print(f"\n📊 Total de usuários: {len(users)}")
        print("\nUsuários cadastrados:")
        print("-" * 60)
        for user in users:
            print(f"  ID: {user[0]:<2} | {user[1]:<25} | {user[2]:<30} | {user[3]}")
        
        cursor.close()
        conn.close()
        
        print("\n🔐 Credenciais de acesso:")
        print("-" * 60)
        for user in SAMPLE_USERS:
            print(f"  {user['email']} / {user['password']} ({user['role']})")
        
        print("\n✅ Seed concluído com sucesso!")
        return True
        
    except Error as e:
        print(f"❌ Erro de banco de dados: {e}")
        return False
    except Exception as e:
        print(f"❌ Erro inesperado: {e}")
        return False

if __name__ == "__main__":
    success = main()
    sys.exit(0 if success else 1)
