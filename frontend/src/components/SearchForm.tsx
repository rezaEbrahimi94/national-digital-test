'use client';

import React, { useState, useEffect } from 'react';

interface SearchFormProps {
  onSearch: (query: string) => void;
  disabled: boolean;
  query: string;
}

const SearchForm: React.FC<SearchFormProps> = ({
  onSearch,
  disabled,
  query,
}) => {
  const [searchQuery, setSearchQuery] = useState(query);

  useEffect(() => {
    setSearchQuery(query);
  }, [query]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSearch(searchQuery);
  };

  return (
    <form onSubmit={handleSubmit} className="flex flex-col gap-2">
      <input
        type="text"
        value={searchQuery}
        onChange={(e) => setSearchQuery(e.target.value)}
        className="border border-gray-300 p-2"
        placeholder="Search..."
        disabled={disabled}
      />
      <button
        type="submit"
        className="bg-blue-500 text-white p-2"
        disabled={disabled}
      >
        Search
      </button>
    </form>
  );
};

export default SearchForm;
