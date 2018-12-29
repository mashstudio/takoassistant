--
-- Create table: location_table
--

CREATE TABLE location_table (
    pkey integer NOT NULL,
    name text,
    keyword text,
    fullname text,
    latitude double precision,
    longitude double precision,
    rating double precision,
    delflag integer DEFAULT 0
);

--
-- Create table: weather_table
--

CREATE TABLE weather_table (
    pkey integer NOT NULL,
    location_table_pkey integer,
    date date,
    "time" time without time zone,
    wind double precision,
    wind_direction_angle integer,
    wind_direction_jp text
);