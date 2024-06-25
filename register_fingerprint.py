import sqlite3
import time
import sys
import json
from pyfingerprint.pyfingerprint import PyFingerprint

DB_PATH = 'db/final.db'  # Update with the correct path to the database file

def delete_employee(employee_name):
    try:
        conn = sqlite3.connect(DB_PATH)
        cursor = conn.cursor()

        cursor.execute('DELETE FROM employees WHERE Employee_Name = ?', (employee_name,))
        conn.commit()
        conn.close()

        return {'status': 'success', 'message': f'Employee {employee_name} deleted successfully'}

    except Exception as e:
        return {'status': 'error', 'message': str(e)}

def get_fingerprint_template(f, employee_name):
    try:
        print('Waiting for finger...')
        while not f.readImage():
            pass

        f.convertImage(0x01)
        print('Remove finger...')
        time.sleep(2)
        print('Waiting for same finger again...')
        while not f.readImage():
            pass

        f.convertImage(0x02)
        if f.compareCharacteristics() == 0:
            raise Exception('Fingers do not match.')

        template = f.downloadCharacteristics(0x01)
        return template

    except Exception as e:
        delete_employee(employee_name)  # Delete employee on error
        return {'status': 'error', 'message': str(e)}

def enroll_user(employee_name):
    try:
        conn = sqlite3.connect(DB_PATH)
        cursor = conn.cursor()

        # Initialize fingerprint sensor
        f = PyFingerprint('/dev/ttyS0', 57600, 0xFFFFFFFF, 0x00000000)

        # Verify sensor password
        if not f.verifyPassword():
            raise ValueError('The fingerprint sensor password is incorrect!')
        
        # Get fingerprint template
        template = get_fingerprint_template(f, employee_name)
        if 'status' in template and template['status'] == 'error':
            return template

        # Check for existing fingerprints in the database
        cursor.execute('SELECT Employee_Name, Fingerprint_Template FROM employees')
        employees = cursor.fetchall()

        for stored_employee_name, stored_template in employees:
            if stored_template is not None:
                stored_template = list(map(int, stored_template.strip('[]').split(', ')))
                f.uploadCharacteristics(0x01, stored_template)
                if f.compareCharacteristics() > 50:
                    delete_employee(employee_name)  # Delete employee if fingerprint match found
                    return {'status': 'error', 'message': f'Fingerprint already enrolled for {stored_employee_name}'}

        # Insert employee details into database
        cursor.execute('UPDATE employees SET Fingerprint_Template = ? WHERE Employee_Name = ?', (str(template), employee_name))
        conn.commit()
        conn.close()

        return {'status': 'success', 'message': f'Fingerprint for {employee_name} enrolled successfully'}

    except Exception as e:
        delete_employee(employee_name)  # Delete employee on error
        return {'status': 'error', 'message': str(e)}

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print(json.dumps({'status': 'error', 'message': 'Employee name not provided'}))
        sys.exit(1)
    
    employee_name = sys.argv[1]
    result = enroll_user(employee_name)
    print(json.dumps(result))
