let baseURL = 'https://api.whise.eu/';
let user = 'https://api.whise.eu/';
let pass = 'Studio27*WHISE';

function getToken() {
    let url = baseURL + 'token';
    let headers = {
        'Content-Type': 'application/json'
    };
    let body = {
        username: user,
        password: pass
    };

    token = fetch(url, {
        method: 'POST',
        headers: headers,
        body: JSON.stringify(body)
    })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data && data.token) {
                output = { "token": data.token };
            }
        })
        .catch(function(error) {
            output = { "token": "nema" };
        });
}

let output = { "tes": 1 };

getToken()
