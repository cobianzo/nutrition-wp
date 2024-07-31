import { registerBlockType } from "@wordpress/blocks";
import { __ } from "@wordpress/i18n";

registerBlockType("asim/alimento-block", {
  title: __("Alimento Block", "asim"),
  icon: "smiley",
  category: "common",
  edit: () => <div>{__("Hello from Block 1", "asim")}</div>,
  save: () => <div>{__("Hello from Block 1", "asim")}</div>,
});
