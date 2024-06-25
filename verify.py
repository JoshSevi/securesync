import sqlite3
import time
from pyfingerprint.pyfingerprint import PyFingerprint
from gpiozero import LED
import os

DB_PATH = 'db/final.db'

led_green = LED(13)
led_yellow = LED(19)
led_red = LED(26)

def verify_fingerprint():
    try:
        f = PyFingerprint('/dev/ttyS0', 57600, 0xFFFFFFFF, 0x00000000)

        if not f.verifyPassword():
            raise ValueError('The fingerprint sensor password is incorrect!')

        print('Waiting for finger...')

        # Wait for finger to read
        while not f.readImage():
            pass

        # Converts read image to characteristics and stores it in charbuffer 1
        f.convertImage(0x01)

        conn = sqlite3.connect(DB_PATH)
        cursor = conn.cursor()
        cursor.execute('SELECT Employee_ID, Employee_Name, fingerprint_template FROM employees')
        employees = cursor.fetchall()

        matched_employee = None
        for employee in employees:
            stored_template = list(map(int, employee[2].strip('[]').split(', ')))
            f.uploadCharacteristics(0x02, stored_template)  # Compare with charbuffer 2
            if f.compareCharacteristics() > 50:  # Similarity threshold
                matched_employee = employee
                break

        if matched_employee:
            employee_id, employee_name = matched_employee[0], matched_employee[1]
            current_date = time.strftime('%Y-%m-%d')
            current_time = time.strftime('%H:%M')

            cursor.execute('SELECT * FROM attendance WHERE Employee_ID = ? AND Date = ? AND Time_Out IS NULL', (employee_id, current_date))
            attendance_record = cursor.fetchone()

            if attendance_record:
                cursor.execute('UPDATE attendance SET Time_Out = ? WHERE Attendance_ID = ?', (current_time, attendance_record[0]))
                print(f'Time-out recorded for {employee_name} at {current_time}.')
            else:
                cursor.execute('INSERT INTO attendance (Employee_ID, Employee_Name, Time_In, Date) VALUES (?, ?, ?, ?)',
                               (employee_id, employee_name, current_time, current_date))
                print(f'Time-in recorded for {employee_name} at {current_time}.')

            conn.commit()
            led_green.on()
            time.sleep(2)
            led_green.off()
        else:
            print('Unrecognized fingerprint.')
            led_red.on()
            time.sleep(2)
            led_red.off()

        conn.close()

    except Exception as e:
        print('Failed to verify fingerprint!')
        print('Exception message: ' + str(e))
        led_yellow.on()
        time.sleep(2)
        led_yellow.off()
       

def main():
    verify_fingerprint()

if __name__ == '__main__':
    main()