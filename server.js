const express = require('express');
const bodyParser = require('body-parser');
const fs = require('fs');
const path = require('path');

const app = express();
const PORT = 3000;
const usersPath = path.join(__dirname, 'users.json');

app.use(bodyParser.urlencoded({ extended: true }));
app.use(express.static('public'));

// Handle login POST
app.post('/login', (req, res) => {
  const { username, password } = req.body;

  // Load users
  const users = JSON.parse(fs.readFileSync(usersPath));

  // Check user
  if (users[username] && users[username] === password) {
    res.send('✅ Login successful!');
  } else {
    res.send('❌ Invalid credentials.');
  }
});

app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`);
});
