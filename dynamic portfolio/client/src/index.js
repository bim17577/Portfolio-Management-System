// This file serves as the entry point for the JavaScript application, rendering the main component to the DOM.

import React from 'react';
import ReactDOM from 'react-dom';
import App from './App';
import './style.css';

ReactDOM.render(
  <React.StrictMode>
    <App />
  </React.StrictMode>,
  document.getElementById('root')
);