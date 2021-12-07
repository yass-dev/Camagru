import requests
import time

def sendMail():
	import smtplib

	gmail_user = 'yassou200121@gmail.com'
	gmail_password = 'momosouria1974'

	sent_from = gmail_user
	to = ['Hanagharbi99@outlook.fr']
	subject = 'Salut blg SUPER IMPORTANT MESSAGE'
	body = "https://www.hauts-de-seine.gouv.fr/booking/create/12249/1"

	email_text = """\
	From: %s
	To: %s
	Subject: %s

	%s
	""" % (sent_from, ", ".join(to), subject, body)

	try:
		server = smtplib.SMTP_SSL('smtp.gmail.com', 465)
		server.ehlo()
		server.login(gmail_user, gmail_password)
		server.sendmail(sent_from, to, email_text)
		server.close()

		print('Email sent!')
	except:
		print('Something went wrong...')

def check():
	cookies = {'eZSESSID':'7qui5tdh7f0ni2t9oielm00tm7',
	'atuserid':'eyJuYW1lIjoiYXR1c2VyaWQiLCJ2YWwiOiIxZWY1OTAyOS1iN2QwLTQ4YTctYmZjNi0zNzIxMTZjNDU4Y2IiLCJvcHRpb25zIjp7ImVuZCI6IjIwMjMtMDEtMDdUMTQ6NDg6MTkuNDgxWiIsInBhdGgiOiIvIn19; atauthority=eyJuYW1lIjoiYXRhdXRob3JpdHkiLCJ2YWwiOnsiYXV0aG9yaXR5X25hbWUiOiJjbmlsIiwidmlzaXRvcl9tb2RlIjoiZXhlbXB0In0sIm9wdGlvbnMiOnsiZW5kIjoiMjAyMy0wMS0wN1QxNTowNjoxNS41NTBaIiwicGF0aCI6Ii8ifX0=',
	'tarteaucitron':'!facebooklikebox=wait!twittertimeline=wait!dailymotion=wait!youtube=wait'}

	headers = {'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36'}

	print("=>", end="")
	try:
		req = requests.post('https://www.hauts-de-seine.gouv.fr/booking/create/12249/1', headers=headers, data ={'planning':15538, 'nextButton':'Etape suivante'}, cookies=cookies);
		if req.text.find("Il n'existe plus de plage horaire libre pour votre demande de rendez-vous. Veuillez recommencer ultérieurement.") > 0:
			print("Y en a plus")
			return 1
		elif req.text.find("Service surcharg&eacute;") > 0:
			print("surchargé")
			return 1
		else:
			print("success ?")
			print(req.text)
			return 0
	except:
		print("Error")
		return 1

while check() > 0:
	time.sleep(30)
	continue

print("SUCCESSSSS")
sendMail()