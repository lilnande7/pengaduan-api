import axios from "axios";

export default axios.create({
  baseURL: "http://127.0.0.1:8000/api"
});

import api from "./api";

const submit = async () => {
  await api.post("/tickets", {
    name,
    phone,
    category,
    message
  });
}; 