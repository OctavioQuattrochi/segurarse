import axios from "axios";

const API_URL = "http://proyecto_segurarse_app:80";

export const newClient = async (nombre, apellido, dni, fechaNacimiento) => 
{
  try {
    const response = await axios.post(`${API_URL}/cliente/new`, { nombre, apellido, dni, fechaNacimiento });
    return response.data;
  } catch (error) {
    throw new Error(error.response?.data?.error || "An error occurred");
  }

};

