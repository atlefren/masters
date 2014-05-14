--
-- Route Tables
--
CREATE TABLE dnt_routes (
	kode character varying(255) NOT NULL,
	navn character varying(255),
	lengde character varying(255),
	sesong character varying(255),
	omraade character varying(255),
	beskrivelse text,
	PRIMARY KEY (kode)
);

CREATE TABLE route_data (
	gid integer NOT NULL,
	dato bigint,
	the_geom geometry,
	CONSTRAINT enforce_dims_the_geom CHECK ((ndims(the_geom) = 2)),
	CONSTRAINT enforce_geotype_the_geom CHECK (((geometrytype(the_geom) = 'MULTILINESTRING'::text) OR (the_geom IS NULL))),
	CONSTRAINT enforce_srid_the_geom CHECK ((srid(the_geom) = 4326)),
	PRIMARY KEY (gid)
);

CREATE TABLE dnt_route_parts (
	gid integer NOT NULL,
	dnt character varying(20) NOT NULL,
	PRIMARY KEY (gid, dnt)
);

--
-- Trips
--
CREATE TABLE trips (
	id serial NOT NULL,
	name character varying(255),
	description character varying(255),
	owner character varying(255),
	PRIMARY KEY (id)
);

CREATE TABLE trip_parts (
	tripid integer NOT NULL,
	dntkode character varying(20) NOT NULL,
	position integer NOT NULL,
	PRIMARY KEY (tripid, dntkode, position)
);

--
-- Images
--
CREATE TABLE images (
	id serial NOT NULL,
	title character varying(255),
	filename character varying(255),
	username character varying(255),
	season integer,
	description character varying(255),
	the_geom geometry,
	CONSTRAINT enforce_dims_the_geom CHECK ((ndims(the_geom) = 2)),
	CONSTRAINT enforce_geotype_the_geom CHECK (((geometrytype(the_geom) = 'POINT'::text) OR (the_geom IS NULL))),
	CONSTRAINT enforce_srid_the_geom CHECK ((srid(the_geom) = 4326)),
	PRIMARY KEY (id)
);

--
-- Pois
--
CREATE TABLE pois (
	id serial NOT NULL,
	poi_name character varying(255),
	poi_type character varying(255),
	the_geom geometry,
	CONSTRAINT enforce_dims_the_geom CHECK ((ndims(the_geom) = 2)),
	CONSTRAINT enforce_geotype_the_geom CHECK (((geometrytype(the_geom) = 'POINT'::text) OR (the_geom IS NULL))),
	CONSTRAINT enforce_srid_the_geom CHECK ((srid(the_geom) = 4326)),
	PRIMARY KEY (id)
);

