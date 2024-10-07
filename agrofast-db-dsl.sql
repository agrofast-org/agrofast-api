CREATE OR REPLACE FUNCTION generate_four_digit_auth_code() RETURNS TEXT AS $$ BEGIN RETURN CAST(FLOOR(1000 + RANDOM() * 9000) AS TEXT); END; $$ LANGUAGE plpgsql;
CREATE SCHEMA IF NOT EXISTS "hr";
CREATE SCHEMA IF NOT EXISTS "transport";
CREATE SCHEMA IF NOT EXISTS "chat";
CREATE TABLE IF NOT EXISTS "hr"."auth_code" (
	"id" SERIAL NOT NULL PRIMARY KEY UNIQUE,
	"user_id" INTEGER NOT NULL,
	"code" TEXT NOT NULL DEFAULT generate_four_digit_auth_code(),
	"active" BOOLEAN NULL DEFAULT TRUE,
	"created_in" TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	"updated_in" TIMESTAMP NULL,
	"inactivated_in" TIMESTAMP NULL
);
CREATE TABLE IF NOT EXISTS "hr"."contact_type" (
	"id" SERIAL NOT NULL PRIMARY KEY UNIQUE,
	"label" TEXT NOT NULL UNIQUE
);
CREATE TABLE IF NOT EXISTS "hr"."document" (
	"id" SERIAL NOT NULL PRIMARY KEY UNIQUE,
	"user_id" INTEGER NOT NULL,
	"document_type" INTEGER NOT NULL,
	"document" TEXT NOT NULL UNIQUE,
	"active" BOOLEAN NULL DEFAULT TRUE,
	"created_in" TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	"updated_in" TIMESTAMP NULL,
	"inactivated_in" TIMESTAMP NULL
);
CREATE TABLE IF NOT EXISTS "hr"."document_type" (
	"id" SERIAL NOT NULL PRIMARY KEY UNIQUE,
	"label" TEXT NOT NULL UNIQUE
);
CREATE TABLE IF NOT EXISTS "hr"."profile" (
	"id" SERIAL NOT NULL PRIMARY KEY UNIQUE,
	"user_id" INTEGER NOT NULL,
	"profile_type" INTEGER NOT NULL,
	"active" BOOLEAN NULL DEFAULT TRUE,
	"created_in" TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	"updated_in" TIMESTAMP NULL,
	"inactivated_in" TIMESTAMP NULL
);
CREATE TABLE IF NOT EXISTS "hr"."profile_type" (
	"id" SERIAL NOT NULL PRIMARY KEY UNIQUE,
	"label" TEXT NOT NULL UNIQUE
);
CREATE TABLE IF NOT EXISTS "hr"."session" (
	"id" SERIAL NOT NULL PRIMARY KEY UNIQUE,
	"user_id" INTEGER NOT NULL,
	"token" TEXT NOT NULL UNIQUE,
	"active" BOOLEAN NULL DEFAULT TRUE,
	"created_in" TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	"updated_in" TIMESTAMP NULL,
	"inactivated_in" TIMESTAMP NULL
);
CREATE TABLE IF NOT EXISTS "hr"."user" (
	"id" SERIAL NOT NULL PRIMARY KEY UNIQUE,
	"name" TEXT NOT NULL,
	"number" TEXT NOT NULL UNIQUE,
	"password" TEXT NOT NULL,
	"authenticated" BOOLEAN NULL DEFAULT FALSE,
	"active" BOOLEAN NOT NULL DEFAULT TRUE,
	"created_in" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	"updated_in" TIMESTAMP NULL,
	"inactivated_in" TIMESTAMP NULL
);
CREATE TABLE IF NOT EXISTS "hr"."user_settings" (
	"id" SERIAL NOT NULL PRIMARY KEY UNIQUE,
	"user_id" INTEGER NULL UNIQUE,
	"theme" TEXT NULL,
	"language" TEXT NULL
);
CREATE TABLE IF NOT EXISTS "transport"."machinery" (
	"id" SERIAL NOT NULL PRIMARY KEY UNIQUE,
	"name" TEXT NOT NULL,
	"model" TEXT NOT NULL,
	"plate" TEXT NOT NULL,
	"user_id" INTEGER NOT NULL,
	"active" BOOLEAN NULL DEFAULT TRUE,
	"created_in" TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	"updated_in" TIMESTAMP NULL,
	"inactivated_in" TIMESTAMP NULL
);
CREATE TABLE IF NOT EXISTS "transport"."offer" (
	"id" SERIAL NOT NULL PRIMARY KEY UNIQUE,
	"user_id" INTEGER NOT NULL,
	"request_id" INTEGER NOT NULL,
	"carrier_id" INTEGER NOT NULL,
	"price" NUMERIC NOT NULL,
	"active" BOOLEAN NULL DEFAULT TRUE,
	"created_in" TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	"updated_in" TIMESTAMP NULL,
	"inactivated_in" TIMESTAMP NULL
);
CREATE TABLE IF NOT EXISTS "transport"."request" (
	"id" SERIAL NOT NULL PRIMARY KEY UNIQUE,
	"user_id" INTEGER NOT NULL,
	"origin" TEXT NOT NULL,
	"destination" TEXT NOT NULL,
	"desired_date" TIMESTAMP NOT NULL,
	"active" BOOLEAN NULL DEFAULT TRUE,
	"created_in" TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	"updated_in" TIMESTAMP NULL,
	"inactivated_in" TIMESTAMP NULL
);
CREATE TABLE IF NOT EXISTS "transport"."carrier" (
	"id" SERIAL NOT NULL PRIMARY KEY UNIQUE,
	"user_id" INTEGER NOT NULL,
	"name" TEXT NOT NULL,
	"model" TEXT NOT NULL,
	"plate" TEXT NOT NULL UNIQUE,
	"active" BOOLEAN NULL DEFAULT TRUE,
	"created_in" TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	"updated_in" TIMESTAMP NULL,
	"inactivated_in" TIMESTAMP NULL
);
CREATE TABLE IF NOT EXISTS "chat"."message" (
	"id" SERIAL NOT NULL PRIMARY KEY UNIQUE,
	"from_user_id" INTEGER NOT NULL,
	"to_user_id" INTEGER NOT NULL,
	"answer_to" INTEGER NULL,
	"message" TEXT NOT NULL,
	"active" BOOLEAN NULL DEFAULT TRUE,
	"created_in" TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	"updated_in" TIMESTAMP NULL,
	"inactivated_in" TIMESTAMP NULL
);
ALTER TABLE "hr"."auth_code" ADD CONSTRAINT fk_auth_code_user_id FOREIGN KEY ("user_id") REFERENCES "hr"."user"("id");
ALTER TABLE "hr"."document" ADD CONSTRAINT fk_document_user_id FOREIGN KEY ("user_id") REFERENCES "hr"."user"("id");
ALTER TABLE "hr"."document" ADD CONSTRAINT fk_document_document_type FOREIGN KEY ("document_type") REFERENCES "hr"."document_type"("id");
ALTER TABLE "hr"."profile" ADD CONSTRAINT fk_profile_user_id FOREIGN KEY ("user_id") REFERENCES "hr"."user"("id");
ALTER TABLE "hr"."profile" ADD CONSTRAINT fk_profile_profile_type FOREIGN KEY ("profile_type") REFERENCES "hr"."profile_type"("id");
ALTER TABLE "hr"."session" ADD CONSTRAINT fk_session_user_id FOREIGN KEY ("user_id") REFERENCES "hr"."user"("id");
ALTER TABLE "hr"."user_settings" ADD CONSTRAINT fk_user_settings_user_id FOREIGN KEY ("user_id") REFERENCES "hr"."user"("id");
ALTER TABLE "transport"."machinery" ADD CONSTRAINT fk_machinery_user_id FOREIGN KEY ("user_id") REFERENCES "hr"."user"("id");
ALTER TABLE "transport"."offer" ADD CONSTRAINT fk_offer_user_id FOREIGN KEY ("user_id") REFERENCES "hr"."user"("id");
ALTER TABLE "transport"."offer" ADD CONSTRAINT fk_offer_request_id FOREIGN KEY ("request_id") REFERENCES "transport"."request"("id");
ALTER TABLE "transport"."offer" ADD CONSTRAINT fk_offer_carrier_id FOREIGN KEY ("carrier_id") REFERENCES "transport"."carrier"("id");
ALTER TABLE "transport"."request" ADD CONSTRAINT fk_request_user_id FOREIGN KEY ("user_id") REFERENCES "hr"."user"("id");
ALTER TABLE "transport"."carrier" ADD CONSTRAINT fk_carrier_user_id FOREIGN KEY ("user_id") REFERENCES "hr"."user"("id");
ALTER TABLE "chat"."message" ADD CONSTRAINT fk_message_from_user_id FOREIGN KEY ("from_user_id") REFERENCES "hr"."user"("id");
ALTER TABLE "chat"."message" ADD CONSTRAINT fk_message_to_user_id FOREIGN KEY ("to_user_id") REFERENCES "hr"."user"("id");
ALTER TABLE "chat"."message" ADD CONSTRAINT fk_message_answer_to FOREIGN KEY ("answer_to") REFERENCES "chat"."message"("id");
