import { useState } from 'react'
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom';
import reactLogo from './assets/react.svg'
import viteLogo from '/vite.svg'
import './App.css'
import ClientForm from './Components/ClientForm'

function App() {
  const [count, setCount] = useState(0)

  return (
    <Router>
      <Routes>
        <Route path="/cliente/new" element={<ClientForm />} />
        {/* <Route path="/" element={<Login />} /> */}
      </Routes>
    </Router>
    
  )
}

export default App
