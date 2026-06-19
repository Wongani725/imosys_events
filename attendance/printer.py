#!/usr/bin/python3



import os
import time
import csv
import json
import requests
import sys
sys.path.insert(0, './zebra')
from zebra import Zebra
from time import sleep

DEV = "JCB19960418VYEPEWA4"
base_dir = "/var/www/html/attendance/"

printer = 'Zebra_Technologies_ZTC_GK420d'

print_queue_api_url = "https://dev.imosys.mw/icam/api/get-pending-participants"
update_queue_api_url = "https://dev.imosys.mw/icam/api/participants/update-status"
api_headers = {
    "Accept": "application/json",
    "Authorization": "Bearer 4|sZ2q0dx9NtWtXqNAxEKgTTohVieui8lHsH25tY4q"
}


def print_label(name, unique_id):
   coupon = "MEAL COUPON"

   z= Zebra(printer)
   z.setqueue(printer)
   z.setup(direct_thermal=True, label_height=(50,10), label_width=80)
   z.store_graphic('logo', f'{base_dir}logo.pcx')

   label = f"""
N
q380
Q240,24+0
S3
D10
A0,8,0,4,1,1,R,"ICAM-{coupon}"
A0,47,0,1,1,1,N,"{name} -15/04/2023"
B0,71,0,1,2,2,63,B,"Powered By iMoSyS"
P1
"""

# .format(str(barcode))
   z.output(label)


def print_queue():
    payload = {
        "id": "1"
    }

    response = requests.get(print_queue_api_url, headers=api_headers, json=payload)
    
    print("Response Status Code:", response.status_code)
    print("Response Content:", response.text)
    
    if response.status_code == 201:
        data = response.json()

        status = data['status']
        if status == 1:
            # loop through the queue
            queue =  data['data']['queue']
            print("Data available")

            for coupon in queue:
                id = coupon['id']
                total_meals = coupon['total_meal_coupons']
                name = coupon['participant_name']
                reference = coupon['participant_reference']
              
                # loop through total meals for an individual
                for x in range(int(f"{total_meals}")):
                    print_label(name, "12345678")

                # update meal coupon print queue
                update_print_queue(id, 'printed')
        else:
            print("No Data")
    else:
        print("Error: Could not retrieve data from the API.")


def update_print_queue(id, status):
    payload = {
        "id": id,
        "status": status
    }

    response = requests.post(update_queue_api_url, headers=api_headers, json=payload)
    
    print("Response Status Code:", response.status_code)
    print("Response Content:", response.text)
    
    if response.status_code == 201:
        data = response.json()
        print(f"Print Queue updated to {status}")
        # return data

    else:
        print("Error: Could not retrieve data from the API.")
        # return None

# print_queue()

while True:
    # print("Printing Coupon")
    # print_label("Sarah Msosa", "12345678")
    print_queue()
    sleep(2)