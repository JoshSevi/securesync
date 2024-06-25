import time
from subprocess import Popen

while True:
    print("Verifying fingerprint...")
    try:
        # Call the verify.py script in a subprocess
        process = Popen(['python3', 'verify.py'])
        process.wait()  # Wait for the subprocess to finish
    except Exception as e:
        print(f"Error running verify.py: {e}")

    print("Waiting for next verification...")
    time.sleep(1)  # Adjust sleep time as needed
