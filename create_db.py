import sqlite3
import os

DB_PATH = 'history.db'

def create_database():
    try:
        conn = sqlite3.connect(DB_PATH)
        c = conn.cursor()
        c.execute('''
            CREATE TABLE IF NOT EXISTS employees (
                Employee_ID INTEGER PRIMARY KEY AUTOINCREMENT,
                Employee_Name TEXT NOT NULL,
                Age INTEGER NOT NULL,
                Job_Type TEXT NOT NULL,
                Contact TEXT NOT NULL,
                fingerprint_template BLOB NULL,
                Date TEXT NOT NULL
            )
        ''')

        c.execute('''
            CREATE TABLE IF NOT EXISTS attendance (
                Attendance_ID INTEGER PRIMARY KEY AUTOINCREMENT,
                Employee_ID INTEGER NOT NULL,
                Employee_Name TEXT NOT NULL,
                Time_In TEXT,
                Time_Out TEXT,
                Date TEXT NOT NULL,
                FOREIGN KEY (Employee_ID) REFERENCES employees (Employee_ID)
            )
        ''')

        c.execute('''
            CREATE TABLE IF NOT EXISTS salary_receipts (
                Receipt_ID INTEGER PRIMARY KEY AUTOINCREMENT,
                Employee_ID INTEGER NOT NULL,
                Employee_Name TEXT NOT NULL,
                DaysOfWork INTEGER NOT NULL,
                TotalWorkHours TEXT NOT NULL,
                DateRange TEXT NOT NULL,
                TotalSalary REAL NOT NULL,
                PrintDate TEXT NOT NULL,
                FOREIGN KEY (Employee_ID) REFERENCES employees (Employee_ID)
            )
        ''')

        c.execute('''
            CREATE TABLE IF NOT EXISTS deleted_employees (
                Employee_ID INTEGER PRIMARY KEY AUTOINCREMENT,
                Employee_Name TEXT NOT NULL,
                Age INTEGER NOT NULL,
                Job_Type TEXT NOT NULL,
                Contact TEXT NOT NULL,
                fingerprint_template BLOB NULL,
                Date TEXT NOT NULL
            )
        ''')

        conn.commit()
        conn.close()
    except sqlite3.DatabaseError as e:
        print('Database error:', e)
        print('Attempting to recover the database...')
        recover_database()
        create_database()

def recover_database():
    backup_path = DB_PATH + '.backup'
    if os.path.exists(backup_path):
        os.remove(backup_path)
    os.rename(DB_PATH, backup_path)
    
    conn = sqlite3.connect(DB_PATH)
    backup_conn = sqlite3.connect(backup_path)
    with backup_conn:
        backup_conn.backup(conn)
    conn.close()
    backup_conn.close()

def populate_employees():
    employees = [
        ('Alice', 30, 'Manager', '555-1234', None, '2024-06-13'),
        ('Bob', 25, 'Developer', '555-5678', None, '2024-06-13')
        # Add more employees as needed
    ]

    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.executemany('''
        INSERT INTO employees (Employee_Name, Age, Job_Type, Contact, fingerprint_template, Date)
        VALUES (?, ?, ?, ?, ?, ?)
    ''', employees)
    conn.commit()
    conn.close()

def populate_attendance():
    attendance_records = [
        (1, 'Alice', '09:00:00', '18:00:00', '2024-06-13'),
        (2, 'Bob', '09:30:00', '17:30:00', '2024-06-13')
        # Add more attendance records as needed
    ]

    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.executemany('''
        INSERT INTO attendance (Employee_ID, Employee_Name, Time_In, Time_Out, Date)
        VALUES (?, ?, ?, ?, ?)
    ''', attendance_records)
    conn.commit()
    conn.close()

def populate_salary_receipts():
    salary_receipts = [
        (1, 'Alice', 20, '160 hours', 'June 2024', 5000.00, '2024-06-01'),
        (2, 'Bob', 18, '144 hours', 'June 2024', 4500.00, '2024-06-01')
        # Add more salary receipts as needed
    ]

    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.executemany('''
        INSERT INTO salary_receipts (Employee_ID, Employee_Name, DaysOfWork, TotalWorkHours, DateRange, TotalSalary, PrintDate)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ''', salary_receipts)
    conn.commit()
    conn.close()

def main():
    create_database()
    populate_employees()
    populate_attendance()
    populate_salary_receipts()

if __name__ == "__main__":
    main()
