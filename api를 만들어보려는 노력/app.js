const app = express();

app.get('/', (req, res) => {
    res.send('Hello world!\n');
});

app.get('/users', (req, res) => {
    return res.json(users);
});

app.listen(3000, ()=> {
    console.log('example app listening on port 3000!');
});

let users = [
    {
        id : 1,
        name : 'jung'
    },
    {
        id : 2,
        name : 'hyun'
    },
    {
        id : 3,
        name : 'cha'
    }
]