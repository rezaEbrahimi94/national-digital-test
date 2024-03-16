// Directive to Next.js that this component should only be rendered on the client side.
'use client';

import React, { useState, useEffect } from 'react';

// Define the props structure for the SearchForm component.
interface SearchFormProps {
  onSearch: (query: string) => void; // Function to execute on form submission.
  disabled: boolean; // Flag to enable or disable the form elements.
  query: string; // Current search query, used to set the initial state and update.
}

// SearchForm component provides an input field for users to enter search queries.
const SearchForm: React.FC<SearchFormProps> = ({
  onSearch,
  disabled,
  query,
}) => {
  const [searchQuery, setSearchQuery] = useState(query); // Local state for the input field.

  // Synchronize local state with the prop `query` whenever it changes.
  useEffect(() => {
    setSearchQuery(query);
  }, [query]);

  // Handle form submission and trigger the search operation.
  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault(); // Prevent the default form submission behavior.
    onSearch(searchQuery); // Invoke the onSearch function with the current query.
  };

  // Render the search form with an input field and a submit button.
  return (
    <form onSubmit={handleSubmit} className="flex flex-col gap-2">
      <input
        type="text"
        value={searchQuery}
        onChange={(e) => setSearchQuery(e.target.value)} // Update local state on input change.
        className="border border-gray-300 p-2"
        placeholder="Search..."
        disabled={disabled} // Control input field's disabled state based on the `disabled` prop.
      />
      <button
        type="submit"
        className="bg-blue-500 text-white p-2"
        disabled={disabled} // Control button's disabled state based on the `disabled` prop.
      >
        Search
      </button>
    </form>
  );
};

export default SearchForm;
