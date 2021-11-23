import './App.css';
import { BrowserRouter, Routes, Route } from "react-router-dom";
import Home from './pages/Home/Home';
import ForumCategories from './pages/Forum/Categories/ForumCategories';

function App() {
  return (
    <div className="App">
      <BrowserRouter>
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/index.php" element={<Home />} />
          <Route path="/forum" element={<ForumCategories />} />
          <Route path="/forum.php" element={<ForumCategories />} />
        </Routes>
      </BrowserRouter>,
    </div>
  );
}

export default App;
