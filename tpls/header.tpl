<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LeiwandLock Apaleo Connector</title>
    <style>
        nav ul {
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        nav li {
            list-style: none;
            margin: 0.5em;
            padding: 0;
            font-size: 1.5em;
        }

        @media (min-width: 45em) {
            nav ul {
                flex-direction: row;
            }

            nav li {
                flex: 1;
                font-size: 1em;
            }

            div.property_form {
                font-size: 1.2em;
            }
        }

        nav a {
            display: block;
            padding: 0.4em;
            text-decoration: none;
            font-weight: bold;
            text-align: center;
            border: 1px solid #afafaf;
            border-radius: 10px;
            box-shadow: 0 5px 10px white inset;
            color: #1a1a1a;
            background-color: #ebebeb;
            transition: all .25s ease-in;
        }

        nav li[aria-current] a {
            background-color: #9b9b9b;
            color: #000000;
            font-weight: bold;
        }

        nav a:focus,
        nav a:hover,
        nav li[aria-current] a:focus,
        nav li[aria-current] a:hover {
            color: #9b9b9b;
            background-color: #1a1a1a;
        }

        .property_form_container {
            margin: 0;
            padding: 0;
        }

        .property_form {
            border: 1px solid #afafaf;
            margin: 0.5em;
            padding: 1em;
            font-size: 1.5em;
            display: inline-block;
        }
        #email_form label{
            width: 100px;
            padding-right: 5px;
            display: inline-block;
        }
    </style>
</head>
<body>
