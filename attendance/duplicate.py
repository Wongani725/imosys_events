#! /usr/bin/env python

import os
import time
import csv
import sys
sys.path.insert(0, './zebra')
from zebra import Zebra

DEV = "JCB19960418VYEPEWA4"
base_dir = "/var/www/html/attendance/"

printer = 'Zebra_Technologies_ZTC_GK420d'

name = str(sys.argv[1])
# name = "Jones Blackwell"
copies = "4"
def print_label(index):
   coupon = "SOFT DRINKS/ WATER"

   if index == 0:
      coupon = "LUNCH"
   elif index == 1:
      coupon = "DRINK"
   elif index == 2:
         coupon = "COCKTAIL"
   else:
      coupon = "DINNER"

   z= Zebra(printer)
   z.setqueue(printer)
   z.setup(direct_thermal=True, label_height=(50,10), label_width=80)
   z.store_graphic('logo', f'{base_dir}logo.pcx')
   label = f"""
N
q406
Q203,026
ZT
A19,20,0,4,1,1,N,"iMoSyS-{coupon}-2022/12/15"
B19,50,0,1A,2,5,50,B,"72533728276{index}"
P1
"""

   label = f"""
N
q406
Q203,026
A19,20,0,4,1,1,N,"iMoSyS-{coupon}-"
A19,41,0,4,1,1,N,"<PRICE.0>"
A30,77,0,2,1,1,N,"2022/12/17"
B100,100,0,1,1,1,50,B,"Printed by iMoSyS"
P1
"""

   label = f"""
N
q380
Q240,24+0
S3
D10
A0,8,0,4,1,1,R,"iMoSyS-{coupon}"
A0,47,0,1,1,1,N,"{name} -15/04/2023"
B0,71,0,1,2,2,63,B,"Powered By iMoSyS"
P1
"""

# .format(str(barcode))
   z.output(label)
  
#
if __name__ == '__main__':
   for x in range(int(f"{copies}")):
      print_label(x)


