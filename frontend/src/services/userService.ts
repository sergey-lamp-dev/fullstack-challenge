import axios from 'axios';
import type { User } from '@/types/user.interface';

const API_BASE_URL = 'http://localhost';

export async function getUsers(): Promise<User[]> {
  try {
    const response = await axios.get(`${API_BASE_URL}/users`);
    return response.data;
  } catch (error) {
    console.error(error);
    return [];
  }
}
