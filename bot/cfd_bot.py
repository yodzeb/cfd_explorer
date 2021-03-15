

from tweepy.streaming import StreamListener
from tweepy import OAuthHandler
import json
import datetime
import configparser
import requests
from screenshot import do_screen
import tweepy
import re

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

def get_data(ds, de, config):
    url = api+"?name=&club=&surname=&season=null&dept=&date_start="
    url +=str(ds.day)+"/"+str(ds.month)+"/"+str(ds.year)
    url +="&date_end="
    url +=str(de.day)+"/"+str(de.month)+"/"+str(de.year)
    print ("[+] Fetching "+url)
    r = requests.get(url)
    if(r.status_code == 200 ):
        data = r.json()
        #print (data)
        image = ""
        text = "Pas de vols déclarés ce "+str(ds.day)+"/"+str(ds.month)+"/"+str(ds.year)+" "+chr(0x1F62D)
        if ("stats" in data and "max" in data["stats"] and data["stats"]["all"]["sum"] > 0):
            text = chr(0x1F3C1)+str(data["stats"]["all"]["sum"])+" kilomètres en "+chr(0x1FA82)+"déclarés ce jour à la CFD!\n"
            text+= "Top dpt: "+str(data["stats"]["top_dpt"])+"\n"
            name = re.sub('[\sA-Z]{2,}','',data["stats"]["max_name"])
            text+= "Bravo à "+name+" pour ses "+str(data["stats"]["max"])+" kms"+chr(0x1F3C6)+"\n"#+str(data["stats"]["all"]["max_name"])
            text+= "https://bit.ly/3cz3AfH"
            print (text)
            do_screen()
            image = "test.png"
        tweet(text, "", config)
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
    get_data(date_start, date_end, config)

    
if __name__ == '__main__':
    c=read_config()
    tweet_days(0, c)
