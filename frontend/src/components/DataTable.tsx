// Directive for Next.js to load this component on the client side.
'use client';

import React from 'react';
import ReactPaginate from 'react-paginate';

// Define the data structure for a single repository.
interface Repository {
  id: number;
  name: string;
  full_name: string;
  html_url: string;
  language: string;
  updated_at: string; // Represents the last update time, used for 'activity' sorting.
  pushed_at: string;
  stargazers_count: number; // Represents the number of stars, used for 'popularity' sorting.
}

// Define the props structure for the DataTable component.
interface DataTableProps {
  data: Repository[];
  total: number;
  perPage: number;
  currentPage: number;
  sort: 'name' | 'popularity' | 'activity';
  order: 'asc' | 'desc';
  onSort: (sortField: 'name' | 'popularity' | 'activity') => void;
  onPageChange: (selectedItem: { selected: number }) => void;
}

// DataTable component displays the repositories in a sortable and paginated table.
const DataTable: React.FC<DataTableProps> = ({
  data,
  total,
  perPage,
  currentPage,
  sort,
  order,
  onSort,
  onPageChange,
}) => {
  // Function to render sort icons next to sortable column headers.
  const sortIcon = (column: string) => {
    return column === sort ? (order === 'asc' ? '↑' : '↓') : '';
  };

  return (
    <div className="overflow-x-auto relative shadow-md sm:rounded-lg">
      <table className="w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <thead className="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
          <tr>
            <th scope="col" className="py-3 px-6">
              ID
            </th>
            <th
              scope="col"
              className="py-3 px-6 cursor-pointer"
              onClick={() => onSort('name')}
            >
              Name {sortIcon('name')}
            </th>
            <th
              scope="col"
              className="py-3 px-6 cursor-pointer"
              onClick={() => onSort('popularity')}
            >
              Popularity {sortIcon('popularity')}
            </th>
            <th
              scope="col"
              className="py-3 px-6 cursor-pointer"
              onClick={() => onSort('activity')}
            >
              Activity {sortIcon('activity')}
            </th>
            <th scope="col" className="py-3 px-6">
              Full Name
            </th>
            <th scope="col" className="py-3 px-6">
              HTML URL
            </th>
            <th scope="col" className="py-3 px-6">
              Language
            </th>
            <th scope="col" className="py-3 px-6">
              Pushed At
            </th>
          </tr>
        </thead>
        <tbody>
          {data.map((repo) => (
            <tr
              key={repo.id}
              className="bg-white border-b dark:bg-gray-800 dark:border-gray-700"
            >
              <td className="py-4 px-6">{repo.id}</td>
              <td className="py-4 px-6">{repo.name}</td>
              <td className="py-4 px-6">{repo.stargazers_count}</td>
              <td className="py-4 px-6">{repo.updated_at}</td>
              <td className="py-4 px-6">{repo.full_name}</td>
              <td className="py-4 px-6">{repo.html_url}</td>
              <td className="py-4 px-6">{repo.language}</td>
              <td className="py-4 px-6">{repo.pushed_at}</td>
            </tr>
          ))}
        </tbody>
      </table>
      <ReactPaginate
        previousLabel={'Previous'}
        nextLabel={'Next'}
        pageCount={Math.ceil(total / perPage)}
        onPageChange={onPageChange}
        containerClassName={'pagination'}
        previousLinkClassName={'pagination__link'}
        nextLinkClassName={'pagination__link'}
        disabledClassName={'pagination__link--disabled'}
        activeClassName={'pagination__link--active'}
        forcePage={currentPage - 1}
      />
    </div>
  );
};

export default DataTable;
