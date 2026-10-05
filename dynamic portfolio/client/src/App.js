import React from 'react';
import { BrowserRouter as Router, Route, Switch } from 'react-router-dom';
import Header from './components/Header.html';
import Footer from './components/Footer.html';
import Navbar from './components/Navbar.html';
import Home from './pages/home.html';
import About from './pages/about.html';
import Projects from './pages/projects.html';
import Services from './pages/services.html';
import Contact from './pages/contact.html';
import Portfolio from './pages/portfolio.html';

const App = () => {
    return (
        <Router>
            <div>
                <Header />
                <Navbar />
                <Switch>
                    <Route path="/" exact component={Home} />
                    <Route path="/about" component={About} />
                    <Route path="/projects" component={Projects} />
                    <Route path="/services" component={Services} />
                    <Route path="/contact" component={Contact} />
                    <Route path="/portfolio" component={Portfolio} />
                </Switch>
                <Footer />
            </div>
        </Router>
    );
};

export default App;