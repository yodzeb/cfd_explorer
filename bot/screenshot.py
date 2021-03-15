import sys

from selenium import webdriver
from pyvirtualdisplay import Display
import unittest
import util
import time

class Test(unittest.TestCase):
    """ Demonstration: Get Chrome to generate fullscreen screenshot """

    def setUp(self):
        display = Display(visible=0, size=(1280, 1024))
        display.start()
        service_log_path = "chromedriver.log"
        service_args = ['--verbose']
        chrome_options = webdriver.ChromeOptions()
        chrome_options.add_argument('--no-sandbox')
        chrome_options.add_argument('--window-size=1280,1024')
        chrome_options.add_argument('--headless')
        chrome_options.add_argument('--disable-gpu')
        
        self.driver = webdriver.Chrome("/usr/lib/chromium-browser/chromedriver",
                                       service_args=service_args,
                                       service_log_path=service_log_path,
                                       chrome_options=chrome_options)

    def tearDown(self):
        self.driver.quit()

    def test_fullpage_screenshot(self):
        ''' Generate document-height screenshot '''
        url = "http://cfd.wiro.fr/?noform=1#last0"
        self.driver.get(url)
        time.sleep(15)
        util.fullpage_screenshot(self.driver, "test.png")

        
def do_screen():
    #unittest.main()
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
    url = "http://cfd.wiro.fr/?noform=1#last0"
    driver.get(url)
    time.sleep(15)
    util.fullpage_screenshot(driver, "test.png")

    
if __name__ == "__main__":
    unittest.main()#argv=[sys.argv[0]])

