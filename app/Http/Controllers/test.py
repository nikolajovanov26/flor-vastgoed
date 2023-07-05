import requests

baseURL = 'https://api.whise.eu/'
user = 'vincent@studio27.be'
password = 'Studio27*WHISE'

try:
    url = baseURL + 'token'
    headers = {
        'Content-Type': 'application/json'
    }
    body = {
        'username': user,
        'password': password
    }

    try:
        response = requests.post(url, json=body, headers=headers)
        data = response.json()

        if response.ok and data and 'token' in data:
            token = data['token']
    except Exception as e:
        return [{'error': e}]
except Exception as e:
    return [{'error': e}]

try:
    url = baseURL + 'v1/estates/list'
    headers = {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token
    }

    body = {
        "Filter" : {
            "ReferenceNumber": '12345678'
        }
    }

    try:
        response = requests.post(url, json=body, headers=headers)
        data = response.json()

        if response.ok and data:
            return [{'estate': data['estates'][0]}]
    except Exception as e:
        return [{'error': e}]
except Exception as e:
    return [{'error': e}]


output = [{'data': 'no-data'}]
