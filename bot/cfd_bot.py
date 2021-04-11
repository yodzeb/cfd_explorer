
import sys
from tweepy.streaming import StreamListener
from tweepy import OAuthHandler
import json
import datetime
from datetime import datetime, timedelta
import configparser
import requests
from screenshot import do_screen
import tweepy
import re
import getopt

conf="./cfd_bot.conf";
api = "http://cfd.wiro.fr/cgi/get_xls.php"

def read_config(p):
    config = configparser.ConfigParser()
    config.read(conf)
    c=0;
    if (p in config):
        print ("[+] Loading profile "+p);
        c=config[p];
    else:
        print ("[!] Missing default section");
        sys.exit(-1);
    return c

def date_ago(ago):
    tod = datetime.now()
    d = timedelta(days = ago)
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
        image = ""
        text = "Pas de vol CFD déclaré ce "+str(ds.day)+"/"+str(ds.month)+"/"+str(ds.year)+" "+chr(0x1F62D)+"\nA demain !"+chr(0x1F609)
        test = "...zzzZZZzzzZZZzzz..."
        if ("stats" in data and "max" in data["stats"] and data["stats"]["all"]["sum"] > 0):
            text = chr(0x1F3C1)+" "+str(data["stats"]["all"]["sum"])+" kilomètres et "
            text+= str(data["stats"]["all"]["count"])+" vols en "+chr(0x1FA82)+" déclarés ce "
            text+= str(ds.day)+"/"+str(ds.month)+"/"+str(ds.year)+" à la CFD!\n"
            text+= "Top departements: "+str(data["stats"]["top_dpt"])+"\n"
            name = re.sub('[\sA-Z\-]{3,}','',data["stats"]["max_name"])
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
            text+= "http://cfd.wiro.fr/?date="+ds_t+"\n"
            text+= "https://parapente.ffvl.fr/cfd/liste/last"
            print (text)
            do_screen(ds_t)
            image = "test.png"
        tweet(text, image, config)
        print (text)


def tweet(text, image, config):
    auth = tweepy.OAuthHandler(config["Consumer_token"], config["Consumer_token_secret"])
    auth.set_access_token(config["Access_Token"], config["Access_token_secret"])
    api = tweepy.API(auth)
    if (image ==""):
        api.update_status(text)
    else:
        api.update_with_media(image, text)

def tweet_days(ago, config):
    print ("[!] Search days ago")
    date_start = date_ago(ago)
    date_end = date_ago(0)
    get_data(date_start, date_end, config)
    sys.exit(0)

def tweet_day(day, config):
    print ("[!] Search days")
    d=date_day(day)
    get_data(d,d,config)
    sys.exit(0)
    
if __name__ == '__main__':
    try:
        day=""
        today=False;
        profile="DEFAULT"
        opts, args = getopt.getopt(sys.argv[1:], "td:m:p:", ["today", "day=", "month=", "profile="])
        for o,a in opts:
            print (o)
            if (o in ("-d", "--day")):
                day = a
                today = False
            if (o in ("-t", "--today")):
                today = True
            if (o in ("-p", "--profile")):
                profile = a
        c=read_config(profile)
        if (day != ""):
            tweet_day(a, c)
        elif (today):
            tweet_days(0, c)
        else:
            print ("[!] Nothing to do...")
    except :
        e = sys.exc_info()[0]
        print( "<p>Error: %s</p>" % e )
        print ("except")

