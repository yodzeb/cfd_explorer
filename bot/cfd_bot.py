
import sys
from tweepy.streaming import StreamListener
from tweepy import OAuthHandler
import json
import datetime
from datetime import datetime
import configparser
import requests
from screenshot import do_screen
import tweepy
import re
import getopt

conf="./cfd_bot.conf";
api = "http://cfd.wiro.fr/cgi/get_xls.php"

def read_config():
    config = configparser.ConfigParser()
    config.read(conf)
    c=0;
    if ("DEFAULT" in config):
        print ("ok");
        c=config["DEFAULT"];
    else:
        print ("[!] Missing default section");
        sys.exit(-1);
    return c

def date_ago(ago):
    tod = datetime.datetime.now()
    d = datetime.timedelta(days = ago)
    a = tod - d
    return (a.date())

def date_day(day):
    print (day)
    d=datetime.strptime(day, '%d/%m/%Y')
    return d

def get_data(ds, de, config):
    url = api+"?name=&club=&surname=&season=null&dept=&date_start="
    ds_t = str(ds.day)+"/"+str(ds.month)+"/"+str(ds.year) 
    url += ds_t
    url +="&date_end="
    url +=str(de.day)+"/"+str(de.month)+"/"+str(de.year)
    print ("[+] Fetching "+url)
    r = requests.get(url)
    if(r.status_code == 200 ):
        data = r.json()
        #print (data)
        image = ""
        text = "Pas de vols CFD déclarés ce "+str(ds.day)+"/"+str(ds.month)+"/"+str(ds.year)+" "+chr(0x1F62D)+"\nA demain !"+chr(0x1F609)
        if ("stats" in data and "max" in data["stats"] and data["stats"]["all"]["sum"] > 0):
            text = chr(0x1F3C1)+str(data["stats"]["all"]["sum"])+" kilomètres en "+chr(0x1FA82)+" déclarés ce "+str(ds.day)+"/"+str(ds.month)+"/"+str(ds.year)+" à la CFD!\n"
            text+= "Top departements: "+str(data["stats"]["top_dpt"])+"\n"
            name = re.sub('[\sA-Z]{2,}','',data["stats"]["max_name"])
            text+= chr(0x1F3C6)+" Bravo à "+name+" pour ses "+str(data["stats"]["max"])+" kms"
            m=data["stats"]["max"]
            if (m>400):
                text += chr(0x2708) # Plane
            elif (m > 300):
                text += chr(0x1F985) # Eagle
            elif (m>200):
                text += chr(0x1F986) # Duck
            elif (m>100):
                text += chr(0x1F426) # bird
            else:
                text += chr(0x1F414) # chicken
            text+="\n"
            text+= "http://cfd.wiro.fr/?date="+ds_t
            print (text)
            do_screen(ds_t)
            image = "test.png"
        tweet(text, image, config)
        print (text)


def tweet(text, image, config):
    # Authenticate to Twitter
    # Authenticate to Twitter
    print (config["Consumer_token"])
    auth = tweepy.OAuthHandler(config["Consumer_token"], config["Consumer_token_secret"])
    auth.set_access_token(config["Access_Token"], config["Access_token_secret"])
    api = tweepy.API(auth)
    if (image ==""):
        api.update_status(text)
    else:
        api.update_with_media(image, text)

def tweet_days(ago, config):
    date_start = date_ago(ago)
    date_end = date_ago(0)
    get_data(date_start, date_end, config, ago)

def tweet_day(day, config):
    d=date_day(day)
    get_data(d,d,config)
    
if __name__ == '__main__':
    c=read_config()
    try:
        opts, args = getopt.getopt(sys.argv[1:], "d:m:", ["day=", "month="])
        for o,a in opts:
            if (o in ("-d", "--day")):
                tweet_day(a, c)
    except:
        tweet_days(0, c)
