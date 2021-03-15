import sys

from selenium import webdriver
from pyvirtualdisplay import Display
import unittest
import util
import time

        
def do_screen(ago):
    display = Display(visible=0, size=(1280, 1024))
    display.start()
    service_log_path = "chromedriver.log"
    service_args = ['--verbose']
    chrome_options = webdriver.ChromeOptions()
    chrome_options.add_argument('--no-sandbox')
    chrome_options.add_argument('--window-size=1280,1024')
    chrome_options.add_argument('--headless')
    chrome_options.add_argument('--disable-gpu')
    
    driver = webdriver.Chrome("/usr/lib/chromium-browser/chromedriver",
                                   service_args=service_args,
                                   service_log_path=service_log_path,
                                   chrome_options=chrome_options)
    url = "http://cfd.wiro.fr/?noform=1&date="+str(ago)
    driver.get(url)
    time.sleep(10)
    util.fullpage_screenshot(driver, "test.png")
    display.stop()

