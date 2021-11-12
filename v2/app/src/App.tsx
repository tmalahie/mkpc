import './App.css';
import { BrowserRouter, Routes, Route } from "react-router-dom";
import Home from './pages/Home/Home';

function App() {
  return (
    <div className="App">
      <BrowserRouter>
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/index.php" element={<Home />} />
        </Routes>
      </BrowserRouter>,
    </div>
  );
}

export default App;
