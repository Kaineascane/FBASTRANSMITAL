'use client';

import { useEffect } from 'react';

export default function PrintAutoTrigger({ id }: { id: number }) {
  useEffect(() => {
    const key = `printed_${id}`;
    if (!sessionStorage.getItem(key)) {
      sessionStorage.setItem(key, '1');
      window.print();
    }
  }, [id]);

  return null;
}
