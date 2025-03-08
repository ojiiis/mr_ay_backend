3️⃣ Expected API Request (Example)
POST /iv/signup
json
Copy
Edit
{
    "full_name": "John Doe",
    "username": "johndoe123",
    "password": "securePass123",
    "usdt_trc20": "TXYZ12345",
    "eth_erc20": "0x123456789abcdef",
    "bitcoin": "1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa",
    "email": "johndoe@example.com",
    "recovery_question": "What is your first pet's name?",
    "recovery_answer": "Charlie"
}
Response (Success)
json
Copy
Edit
{
    "status": 1,
    "message": "Signup successful"
}
Response (Error: Missing Field)
json
Copy
Edit
{
    "status": 0,
    "message": "username is required"
}
Response (Error: Email/Username Exists)
json
Copy
Edit
{
    "status": 0,
    "message": "Email or username already exists"
}



