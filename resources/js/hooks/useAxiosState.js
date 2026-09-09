import { useCallback, useState } from 'react';
import axios from '../lib/axios';

/**
 * Generic loading/error/data wrapper for one-off axios calls (used instead of
 * a full Inertia visit for the slow, synchronous endpoints: interrogation chat
 * and audio retry) so the rest of the page never blocks while waiting.
 */
export default function useAxiosState() {
    const [data, setData] = useState(null);
    const [error, setError] = useState(null);
    const [loading, setLoading] = useState(false);

    const run = useCallback(async (config) => {
        setLoading(true);
        setError(null);

        try {
            const response = await axios(config);
            setData(response.data);
            return response.data;
        } catch (err) {
            setError(err.response?.data?.message || 'Ocurrió un error inesperado.');
            throw err;
        } finally {
            setLoading(false);
        }
    }, []);

    return { data, error, loading, run };
}
