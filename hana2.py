import requests
import os
import time
import smtplib
import datetime
from lxml import html

def send_email():
	# Configure your proper stmp account
	smtp_server = "smtp.gmail.com"
	port = 587
	sender_email = "yassou200121@gmail.com"
	password = "momosouria"

	# Try to login to server and send email
	try:
		server = smtplib.SMTP(smtp_server, port)
		server.connect(smtp_server, port)
		server.ehlo()
		server.starttls()
		server.ehlo()
		server.login(sender_email, password)
		txt = "https://www.hauts-de-seine.gouv.fr/booking/create/12249/1"
		msg = "Subject: {}\n\n{}".format('GO TO Prefecture', txt)
		server.sendmail(sender_email, "Hanagharbi99@outlook.fr", msg)
		server.quit()
	except Exception as e:
		print(e)

def main():
	sleep_time = 10
	while True:
		# Boucling the fucking French Hauts-de-Seine prefecture foreigner rdv system
		headers = {
		'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:83.0) Gecko/20100101 Firefox/83.0',
		'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
		'Accept-Language': 'fr-FR,en-US;q=0.7,en;q=0.3',
		'Content-Type': 'application/x-www-form-urlencoded',
		'Origin': 'https://www.hauts-de-seine.gouv.fr',
		'DNT': '1',
		'Connection': 'close',
		'Referer': 'https://www.hauts-de-seine.gouv.fr/booking/create/12249/1',
		'Upgrade-Insecure-Requests': '1',
		}

		#planning=16467&nextButton=Etape+suivante
		data = {
		  'planning': '15538',
		  'nextButton': 'Etape suivante'
		}
		
		try:
			with requests.Session() as session:
				for_cookies = session.get("https://www.hauts-de-seine.gouv.fr/booking/create/12249/1")
				cookies = for_cookies.cookies
				response = requests.post('https://www.hauts-de-seine.gouv.fr/booking/create/12249/1', headers=headers, cookies=cookies, data=data)
		except:
			print("Oops! Connection is not available.  Try again...")
			time.sleep(sleep_time)
			continue
		
		if response.status_code == "502":
			print("Bad Gateway")
			time.sleep(sleep_time)
			continue

		page_content_tree = html.fromstring(response.content)
		msg=page_content_tree.xpath("//form[@name='create']")

		if not msg or len(msg) <1:
			print("[I] {} Fxxking prefecture!".format(datetime.datetime.now()))
			response.close()
		else:
			txt=msg[0].text.strip()
			if "plus de plage horaire libre pour votre demande de rendez-vous" in txt:
				print(txt)
				
				#	break
			else:
				send_email()
				print(txt)
				print("Hurry up!")
				break

		time.sleep(sleep_time)

if __name__ == "__main__":
	main()