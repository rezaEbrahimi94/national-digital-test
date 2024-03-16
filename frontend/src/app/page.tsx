// 'use client' indicates this Next.js page should be rendered on the client side.
'use client';

import React, { useEffect, useState, useCallback } from 'react';
import axios from 'axios';
import SearchForm from '../components/SearchForm';
import DataTable from '../components/DataTable';

// Define the structure for the repository data.
interface Repository {
  id: number;
  name: string;
  full_name: string;
  html_url: string;
  language: string;
  updated_at: string;
  pushed_at: string;
  stargazers_count: number;
}

// Specify the structure for the parameters used to fetch repositories.
interface FetchParams {
  search: string;
  sort: 'name' | 'popularity' | 'activity';
  order: 'asc' | 'desc';
  per_page: number;
  page: number;
}

// Default parameters for fetching repositories.
const defaultParams: FetchParams = {
  search: '',
  sort: 'name',
  order: 'asc',
  per_page: 50,
  page: 1,
};

// The Home component is the main page component that displays the repositories.
export default function Home() {
  const [repositories, setRepositories] = useState<Repository[]>([]);
  const [total, setTotal] = useState(0);
  const [isLoading, setIsLoading] = useState(false);
  const [fetchParams, setFetchParams] = useState<FetchParams>(defaultParams);

  // Fetch repositories from the API with the given parameters.
  const fetchRepositories = useCallback(async (params: FetchParams) => {
    setIsLoading(true);
    try {
      const response = await axios.get(`http://localhost:8000/api/repos`, {
        params,
      });
      setRepositories(response.data.data);
      setTotal(response.data.total);
    } catch (error) {
      console.error('Failed to fetch repositories:', error);
    }
    setIsLoading(false);
  }, []);

  // Fetch repositories when the component mounts or fetchParams changes.
  useEffect(() => {
    fetchRepositories(fetchParams);
  }, [fetchParams, fetchRepositories]);

  // Handle the search action and update the fetch parameters.
  const handleSearch = (query: string) => {
    const newParams = { ...fetchParams, search: query, page: 1 };
    setFetchParams(newParams);
  };

  // Handle page change action in pagination.
  const handlePageChange = (selectedItem: { selected: number }) => {
    const newPage = selectedItem.selected + 1;
    if (newPage !== fetchParams.page) {
      setFetchParams({ ...fetchParams, page: newPage });
    }
  };

  // Handle sorting action on different repository fields.
  const handleSort = (sortField: 'name' | 'popularity' | 'activity') => {
    const newOrder = fetchParams.order === 'asc' ? 'desc' : 'asc';
    if (sortField !== fetchParams.sort || newOrder !== fetchParams.order) {
      setFetchParams({ ...fetchParams, sort: sortField, order: newOrder });
    }
  };

  // Display loading state while fetching data.
  if (isLoading) {
    return <div>Loading...</div>;
  }

  // Render the search form and data table with the fetched repository data.
  return (
    <div className="p-4">
      <SearchForm
        onSearch={handleSearch}
        disabled={isLoading}
        query={fetchParams.search}
      />
      <DataTable
        data={repositories}
        total={total}
        perPage={fetchParams.per_page}
        currentPage={fetchParams.page}
        sort={fetchParams.sort}
        order={fetchParams.order}
        onSort={handleSort}
        onPageChange={handlePageChange}
      />
    </div>
  );
}
