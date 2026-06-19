import requests
import json

def read_api(url, headers, payload):
    response = requests.post(url, headers=headers, json=payload)
    
    print("Response Status Code:", response.status_code)
    print("Response Content:", response.text)
    
    if response.status_code == 200:
        data = response.json()
        return data
    else:
        print("Error: Could not retrieve data from the API.")
        return None

# Example usage
api_url = "https://dev.imosys.mw/icam/api/participants/update-status"
api_headers = {
    "Accept": "application/json",
    "Authorization": "Bearer 4|sZ2q0dx9NtWtXqNAxEKgTTohVieui8lHsH25tY4q"
}

# Example payload for the POST request
payload = {
    "id": "1"
}

data = read_api(api_url, api_headers, payload)

if data:
    # Process the data
    print("Received data from the API:")
    print(json.dumps(data, indent=4))
